<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

// controller for managing job listings and credits
class JobController extends Controller
{
    // display list of available jobs
    public function index()
    {
        // get current user id if logged in
        $userId = auth()->check() ? auth()->id() : null;
        
        // get jobs without submissions
        $jobsQuery = Job::with('user')
            ->whereDoesntHave('submissions')
            ->from('job_listings');

        // handle search
        if ($search = request('search')) {
            $search = strtolower($search);
            $jobsQuery->where(function ($query) use ($search) {
                $query->whereRaw("LOWER(title) LIKE ?", ["%{$search}%"])
                    ->orWhereRaw("LOWER(description) LIKE ?", ["%{$search}%"])
                    ->orWhereHas('user', function ($query) use ($search) {
                        $query->whereRaw("LOWER(first_name) LIKE ?", ["%{$search}%"])
                            ->orWhereRaw("LOWER(last_name) LIKE ?", ["%{$search}%"]);
                    });
            });
        }

        // handle category filter
        if ($category = request('category')) {
            $categories = config('job_categories', []);
            // if top-level category selected, match subcategories too
            if (array_key_exists($category, $categories)) {
                $jobsQuery->where(function ($q) use ($category) {
                    $q->where('category', $category)
                      ->orWhere('category', 'like', $category . '.%');
                });
            } else {
                $jobsQuery->where('category', $category);
            }
        }

        // handle status filter
        if ($status = request('status')) {
            $jobsQuery->whereHas('submissions', function ($query) use ($status) {
                $query->where('status', $status);
            });
        }

        // handle numeric credit filters
        if (($min = request('min_credits')) !== null) {
            $jobsQuery->where('time_credits', '>=', intval($min));
        }

        if (($max = request('max_credits')) !== null) {
            $jobsQuery->where('time_credits', '<=', intval($max));
        }

        // handle sorting
        $sort = request('sort', 'latest');

        switch ($sort) {
            case 'latest':
                $jobsQuery->orderBy('created_at', 'desc');
                break;
            case 'created_asc':
                $jobsQuery->orderBy('created_at', 'asc');
                break;
            case 'title_asc':
                $jobsQuery->orderBy('title', 'asc');
                break;
            case 'title_desc':
                $jobsQuery->orderBy('title', 'desc');
                break;
            case 'cheapest':
                $jobsQuery->orderBy('time_credits', 'asc');
                break;
            case 'expensive':
                $jobsQuery->orderBy('time_credits', 'desc');
                break;
            case 'seller_most_credits':
                // join users table to order by seller's credits
                $jobsQuery->leftJoin('users', 'job_listings.user_id', '=', 'users.id')
                          ->select('job_listings.*')
                          ->orderBy('users.time_credits', 'desc');
                break;
            default:
                $jobsQuery->orderBy('created_at', 'desc');
                break;
        }

        // paginate results
        $jobs = $jobsQuery->paginate(10);

        return view('jobs.index', [
            'jobs' => $jobs,
            'search' => request('search'),
            'sort' => $sort,
            'categories' => config('job_categories')
        ]);
    }

    // show job creation form
    public function create()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return view('jobs.create', ['categories' => config('job_categories')]);
    }

    // display job details
    public function show(Job $job)
    {
        return view('jobs.show', [
            'job' => $job,
            'categories' => config('job_categories')
        ]);
    }

    // create a new job listing
    public function store()
    {
        // validate job creation input
        // build allowed category list from config
        $categories = config('job_categories', []);
        $allowed = [];
        foreach ($categories as $key => $group) {
            $allowed[] = $key;
            if (!empty($group['children']) && is_array($group['children'])) {
                $allowed = array_merge($allowed, array_keys($group['children']));
            }
        }

        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', Rule::in($allowed)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if (request()->hasFile('image')) {
            $attributes['image_path'] = request()->file('image')->store('job-images', 'public');
        }

        unset($attributes['image']);

        $user = auth()->user();

        // check if user has enough credits
        if ($user->time_credits < $attributes['time_credits']) {
            return back()->withErrors([
                'time_credits' => 'Jums nav pietiekami daudz laika kredītu. Lūdzu, papildiniet kredītus vai samaziniet nepieciešamo daudzumu.'
            ])->withInput();
        }

        try {
            // create job in a transaction
            $job = DB::transaction(function () use ($attributes, $user) {
                // create job listing
                $attributes['user_id'] = $user->id;
                $job = Job::create($attributes);

                // deduct credits from user
                $user->update([
                    'time_credits' => $user->time_credits - $attributes['time_credits']
                ]);

                // log transaction
                DB::table('transactions')->insert([
                    'user_id' => $user->id,
                    'amount' => -$attributes['time_credits'],
                    'description' => "Izveidots darba sludinājums: {$attributes['title']}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return $job;
            });

            return redirect('/jobs')->with('success', 'Serviss veiksmīgi izveidots! ' . $attributes['time_credits'] . ' kredīti ir rezervēti šim darbam.');
        } catch (\Exception $e) {
            // log error and show user-friendly message
            Log::error('Job creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās izveidot sludinājumu: ' . $e->getMessage()
            ])->withInput();
        }
    }

    // show job edit form
    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job, 'categories' => config('job_categories')]);
    }

    // update an existing job listing
    public function update(Job $job)
    {
        // authorize job editing
        Gate::authorize('edit-job', $job);

        // validate update input
        // build allowed category list from config 
        $categories = config('job_categories', []);
        $allowed = [];
        foreach ($categories as $key => $group) {
            $allowed[] = $key;
            if (!empty($group['children']) && is_array($group['children'])) {
                $allowed = array_merge($allowed, array_keys($group['children']));
            }
        }

        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', Rule::in($allowed)],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        if (request()->hasFile('image')) {
            if ($job->image_path) {
                Storage::disk('public')->delete($job->image_path);
            }

            $attributes['image_path'] = request()->file('image')->store('job-images', 'public');
        }

        unset($attributes['image']);

        $user = auth()->user();
        $originalCredits = $job->time_credits;
        $newCredits = $attributes['time_credits'];

        // check if the credits amount has changed
        if ($originalCredits != $newCredits) {
            $netChange = $newCredits - $originalCredits;

            // if the user needs more credits than before
            if ($netChange > 0) {
                if ($user->time_credits < $netChange) {
                    return back()->withErrors([
                        'time_credits' => 'Jums nav pietiekami daudz laika kredītu. Jūsu pašreizējais atlikums ir ' . $user->time_credits . ' kredīti.'
                    ])->withInput();
                }
            }

            try {
                // process the credit adjustment in a transaction
                DB::transaction(function () use ($job, $attributes, $user, $originalCredits, $newCredits) {
                    // first return the original credits to the user
                    $user->update([
                        'time_credits' => $user->time_credits + $originalCredits
                    ]);

                    // record the credit return transaction
                    DB::table('transactions')->insert([
                        'user_id' => $user->id,
                        'amount' => $originalCredits,
                        'description' => "Atgriezti kredīti no atjaunināta darba: {$job->title}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // then deduct the new amount of credits
                    $user->update([
                        'time_credits' => $user->time_credits - $newCredits
                    ]);

                    // record the new credit deduction transaction
                    DB::table('transactions')->insert([
                        'user_id' => $user->id,
                        'amount' => -$newCredits,
                        'description' => "Piešķirti kredīti atjauninātam darbam: {$attributes['title']}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // update the job listing
                    $job->update($attributes);
                });

                return redirect('/jobs/' . $job->id)->with('success', 'Serviss atjaunināts veiksmīgi! Kredītu pielāgojums ir apstrādāts.');
            } catch (\Exception $e) {
                // log error and show message
                Log::error('Job update failed: ' . $e->getMessage());
                
                return back()->withErrors([
                    'error' => 'Neizdevās atjaunināt sludinājumu: ' . $e->getMessage()
                ])->withInput();
            }
        } else {
            // if credits haven't changed, just update the job
            $job->update($attributes);
            return redirect('/jobs/' . $job->id)->with('success', 'Serviss atjaunināts veiksmīgi!');
        }
    }

    // delete a job listing and return credits to the owner
    public function destroy(Job $job)
    {
        // authorize job deletion
        Gate::authorize('edit-job', $job);
        
        try {
            DB::transaction(function () use ($job) {
                // get the job owner
                $jobOwner = User::find($job->user_id);
                
                if ($jobOwner) {
                    // return credits to job owner
                    $jobOwner->update([
                        'time_credits' => $jobOwner->time_credits + $job->time_credits
                    ]);
                    
                    // record credit return transaction
                    DB::table('transactions')->insert([
                        'user_id' => $jobOwner->id,
                        'amount' => $job->time_credits,
                        'description' => "Atgriezti kredīti no dzēsta darba: {$job->title}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // delete the job listing
                if ($job->image_path) {
                    Storage::disk('public')->delete($job->image_path);
                }

                $job->delete();
            });
            
            return redirect('/jobs')->with('success', 'Serviss veiksmīgi dzēsts un kredīti ir atgriezti!');
        } catch (\Exception $e) {
            // log error and show user-friendly message
            Log::error(message: 'Job deletion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Neizdevās dzēst sludinājumu: ' . $e->getMessage()
            ]);
        }
    }
}
