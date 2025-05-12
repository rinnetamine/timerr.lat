<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
            $jobsQuery->where('category', $category);
        }

        // handle status filter
        if ($status = request('status')) {
            $jobsQuery->whereHas('submissions', function ($query) use ($status) {
                $query->where('status', $status);
            });
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
            default:
                $jobsQuery->orderBy('created_at', 'desc');
                break;
        }

        // paginate results
        $jobs = $jobsQuery->paginate(10);

        return view('jobs.index', [
            'jobs' => $jobs,
            'search' => request('search'),
            'sort' => $sort
        ]);
    }

    // show job creation form
    public function create()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return view('jobs.create');
    }

    // display job details
    public function show(Job $job)
    {
        return view('jobs.show', ['job' => $job]);
    }

    // create a new job listing
    public function store()
    {
        // validate job creation input
        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'in:creative,education,professional,technology,other']
        ]);

        $user = auth()->user();

        // check if user has enough credits
        if ($user->time_credits < $attributes['time_credits']) {
            return back()->withErrors([
                'time_credits' => 'You don\'t have enough time credits. Please add more credits or reduce the required amount.'
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
                    'description' => "Created job listing: {$attributes['title']}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                return $job;
            });

            return redirect('/jobs')->with('success', 'Service listing created successfully! ' . $attributes['time_credits'] . ' credits have been reserved for this job.');
        } catch (\Exception $e) {
            // log error and show user-friendly message
            Log::error('Job creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to create listing: ' . $e->getMessage()
            ])->withInput();
        }
    }

    // show job edit form
    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job]);
    }

    // update an existing job listing
    public function update(Job $job)
    {
        // authorize job editing
        Gate::authorize('edit-job', $job);

        // validate update input
        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'in:creative,education,professional,technology,other']
        ]);

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
                        'time_credits' => 'You don\'t have enough time credits. Your current balance is ' . $user->time_credits . ' credits.'
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
                        'description' => "Credits returned from updated job: {$job->title}",
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
                        'description' => "Credits allocated for updated job: {$attributes['title']}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);

                    // update the job listing
                    $job->update($attributes);
                });

                return redirect('/jobs/' . $job->id)->with('success', 'Service updated successfully! Credit adjustment has been processed.');
            } catch (\Exception $e) {
                // log error and show message
                Log::error('Job update failed: ' . $e->getMessage());
                
                return back()->withErrors([
                    'error' => 'Failed to update listing: ' . $e->getMessage()
                ])->withInput();
            }
        } else {
            // if credits haven't changed, just update the job
            $job->update($attributes);
            return redirect('/jobs/' . $job->id)->with('success', 'Service updated successfully!');
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
                        'description' => "Credits returned from deleted job: {$job->title}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // delete the job listing
                $job->delete();
            });
            
            return redirect('/jobs')->with('success', 'Service deleted successfully and credits have been returned!');
        } catch (\Exception $e) {
            // log error and show user-friendly message
            Log::error(message: 'Job deletion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to delete listing: ' . $e->getMessage()
            ]);
        }
    }
}
