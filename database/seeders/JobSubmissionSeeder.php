<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\JobSubmission;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobSubmissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jobs = Job::all();
        $users = User::all();

        // create submissions for jobs
        foreach ($jobs as $job) {
            $submissionCount = rand(0, 3);
            
            for ($i = 0; $i < $submissionCount; $i++) {
                // pick a random user who doesn't own the job
                $submitter = $users->where('id', '!=', $job->user_id)->random();
                
                JobSubmission::factory()->create([
                    'job_listing_id' => $job->id,
                    'user_id' => $submitter->id,
                    'status' => fake()->randomElement([
                        JobSubmission::STATUS_CLAIMED,
                        JobSubmission::STATUS_PENDING,
                        JobSubmission::STATUS_APPROVED,
                        JobSubmission::STATUS_DECLINED,
                        JobSubmission::STATUS_ADMIN_REVIEW
                    ])
                ]);
            }
        }

        // ensure we have some approved submissions for testing
        $approvedCount = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)->count();
        if ($approvedCount < 5) {
            // create some approved submissions if we don't have enough
            $jobs = Job::take(5)->get();
            $users = User::where('id', '!=', $jobs->first()->user_id)->take(5)->get();
            
            foreach ($jobs as $index => $job) {
                if ($index < $users->count()) {
                    JobSubmission::factory()->create([
                        'job_listing_id' => $job->id,
                        'user_id' => $users[$index]->id,
                        'status' => JobSubmission::STATUS_APPROVED,
                        'admin_approved' => true
                    ]);
                }
            }
        }
    }
}
