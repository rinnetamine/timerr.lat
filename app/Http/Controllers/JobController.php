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
        // Get the current user's ID if logged in
        $userId = auth()->check() ? auth()->id() : null;
        
        // Start building the query - only show jobs with NO submissions
        $jobsQuery = Job::with('user')
            ->whereDoesntHave('submissions');
        
        // Get jobs with no submissions
        $jobs = $jobsQuery->latest()->simplePaginate(3);

        return view('jobs.index', [
            'jobs' => $jobs
        ]);
    }

    public function create()
    {
        if (!auth()->check()) {
            return redirect('/login');
        }
        return view('jobs.create');
    }

    public function show(Job $job)
    {
        return view('jobs.show', ['job' => $job]);
    }

    public function store()
    {
        $attributes = request()->validate([
            'title' => ['required', 'min:3'],
            'description' => ['required', 'min:10'],
            'time_credits' => ['required', 'integer', 'min:1'],
            'category' => ['required', 'in:creative,education,professional,technology,other']
        ]);

        $user = auth()->user();

        // Check if user has enough credits
        if ($user->time_credits < $attributes['time_credits']) {
            return back()->withErrors([
                'time_credits' => 'You don\'t have enough time credits. Please add more credits or reduce the required amount.'
            ])->withInput();
        }

        try {
            $job = DB::transaction(function () use ($attributes, $user) {
                // Create the job listing
                $attributes['user_id'] = $user->id;
                $job = Job::create($attributes);

                // Deduct time credits from user's account
                $user->update([
                    'time_credits' => $user->time_credits - $attributes['time_credits']
                ]);

                // Create a transaction record
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
            // Log the actual error for debugging
            Log::error('Job creation failed: ' . $e->getMessage());
            
            return back()->withErrors([
                'error' => 'Failed to create listing: ' . $e->getMessage()
            ])->withInput();
        }
    }

    public function edit(Job $job)
    {
        return view('jobs.edit', ['job' => $job]);
    }

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

    public function destroy(Job $job)
    {
        Gate::authorize('edit-job', $job);
        
        try {
            DB::transaction(function () use ($job) {
                // Get the job owner
                $jobOwner = User::find($job->user_id);
                
                if ($jobOwner) {
                    // Return credits to the job owner
                    $jobOwner->update([
                        'time_credits' => $jobOwner->time_credits + $job->time_credits
                    ]);
                    
                    // Create a transaction record for returned credits
                    DB::table('transactions')->insert([
                        'user_id' => $jobOwner->id,
                        'amount' => $job->time_credits,
                        'description' => "Credits returned from deleted job: {$job->title}",
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
                
                // Delete the job
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
