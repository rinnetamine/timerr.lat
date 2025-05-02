<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JobController extends Controller
{
    public function index()
    {
        // get the current user's id if logged in
        $userId = auth()->check() ? auth()->id() : null;
        
        // only show jobs that don't have any submissions yet
        $jobsQuery = Job::with('user')
            ->whereDoesntHave('submissions');
        
        // paginate results for the view
        $jobs = $jobsQuery->latest()->simplePaginate(3);

        return view('jobs.index', [
            'jobs' => $jobs
        ]);
    }

    // show the form to create a new job listing
    public function create()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return view('jobs.create');
    }

    // display details of a specific job
    public function show(Job $job)
    {
        return view('jobs.show', ['job' => $job]);
    }

    // validates input, deducts credits from user, and creates job
    public function store()
    {
        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'in:creative,education,professional,technology,other']
        ]);

        $user = auth()->user();

        // verify user has enough credits before creating job
        if ($user->time_credits < $attributes['time_credits']) {
            return back()->withErrors([
                'time_credits' => 'You don\'t have enough time credits. Please add more credits or reduce the required amount.'
            ])->withInput();
        }

        try {
            $job = DB::transaction(function () use ($attributes, $user) {
                // create the job listing
                $attributes['user_id'] = $user->id;
                $job = Job::create($attributes);

                // deduct time credits from user's account
                $user->update([
                    'time_credits' => $user->time_credits - $attributes['time_credits']
                ]);

                // record the transaction for audit purposes
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
            // log the actual error for debugging
            Log::error('Job creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to create listing: ' . $e->getMessage()
            ])->withInput();
        }
    }

    // show the form to edit an existing job
    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job]);
    }

    // update an existing job listing
    public function update(Job $job)
    {
        Gate::authorize('edit-job', $job);

        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'in:creative,education,professional,technology,other']
        ]);

        $job->update($attributes);

        return redirect('/jobs/' . $job->id)->with('success', 'Service updated successfully!');
    }

    // delete a job listing and return credits to the owner
    public function destroy(Job $job)
    {
        Gate::authorize('edit-job', $job);
        
        try {
            DB::transaction(function () use ($job) {
                // get the job owner
                $jobOwner = User::find($job->user_id);
                
                if ($jobOwner) {
                    // return the reserved credits back to the job owner
                    $jobOwner->update([
                        'time_credits' => $jobOwner->time_credits + $job->time_credits
                    ]);
                    
                    // record the returned credits transaction
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
            Log::error(message: 'Job deletion failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to delete listing: ' . $e->getMessage()
            ]);
        }
    }
}
