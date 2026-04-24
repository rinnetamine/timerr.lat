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
            $submissionCount = rand(0, 1);
            
            for ($i = 0; $i < $submissionCount; $i++) {
                // pick a random user who doesn't own the job
                $submitter = $users->where('id', '!=', $job->user_id)->random();
                
                $status = fake()->randomElement([
                    JobSubmission::STATUS_CLAIMED,
                    JobSubmission::STATUS_PENDING,
                    JobSubmission::STATUS_APPROVED,
                    JobSubmission::STATUS_DECLINED,
                    JobSubmission::STATUS_ADMIN_REVIEW
                ]);

                $submissionData = [
                    'job_listing_id' => $job->id,
                    'user_id' => $submitter->id,
                    'status' => $status
                ];

                // add dispute data based on status
                if ($status === JobSubmission::STATUS_ADMIN_REVIEW) {
                    // admin review from declined job
                    $submissionData['dispute_status'] = JobSubmission::DISPUTE_NONE;
                    $submissionData['admin_notes'] = fake()->randomElement([
                        'Job owner declined: Worker did not complete the task as specified',
                        'Job owner declined: Poor quality work delivered',
                        'Job owner declined: Missed deadline without communication',
                        'Job owner declined: Incomplete submission'
                    ]);
                } elseif (fake()->boolean(30)) { // 30% chance of having a dispute
                    $disputeType = fake()->randomElement([
                        JobSubmission::DISPUTE_REQUESTED,
                        JobSubmission::DISPUTE_UNDER_REVIEW,
                        JobSubmission::DISPUTE_RESOLVED
                    ]);

                    $submissionData['dispute_status'] = $disputeType;
                    $submissionData['dispute_initiated_by'] = fake()->randomElement([$job->user_id, $submitter->id]);
                    $submissionData['is_frozen'] = $disputeType !== JobSubmission::DISPUTE_NONE;

                    // different dispute reasons based on who initiated
                    if ($submissionData['dispute_initiated_by'] === $job->user_id) {
                        // job owner disputes
                        $submissionData['dispute_reason'] = fake()->randomElement([
                            'Worker submitted incomplete work',
                            'Quality of work does not match requirements',
                            'Worker missed the deadline',
                            'Communication issues during the task',
                            'Worker disappeared after claiming the job'
                        ]);
                        $submissionData['freeze_reason'] = 'Dispute initiated by job owner';
                    } else {
                        // worker disputes
                        $submissionData['dispute_reason'] = fake()->randomElement([
                            'Job owner not responding to messages',
                            'Job owner changed requirements after work started',
                            'Unfair rejection of completed work',
                            'Job owner refusing to approve completed task',
                            'Payment/credit issues with job owner'
                        ]);
                        $submissionData['freeze_reason'] = 'Dispute initiated by worker';
                    }

                    // add resolved data if status is resolved
                    if ($disputeType === JobSubmission::DISPUTE_RESOLVED) {
                        $submissionData['dispute_resolved_by'] = User::where('role', 'admin')->first()->id;
                        $submissionData['dispute_resolved_at'] = fake()->dateTimeBetween('-1 week', 'now');
                        $submissionData['is_frozen'] = false;
                    }
                } else {
                    // no dispute
                    $submissionData['dispute_status'] = JobSubmission::DISPUTE_NONE;
                    $submissionData['is_frozen'] = false;
                }

                JobSubmission::factory()->create($submissionData);
            }
        }

        // ensure we have some approved submissions for testing
        $approvedCount = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)->count();
        if ($approvedCount < 2) {
            // create some approved submissions if we don't have enough
            $jobs = Job::take(2)->get();
            $users = User::where('id', '!=', $jobs->first()->user_id)->take(2)->get();
            
            foreach ($jobs as $index => $job) {
                if ($index < $users->count()) {
                    JobSubmission::factory()->create([
                        'job_listing_id' => $job->id,
                        'user_id' => $users[$index]->id,
                        'status' => JobSubmission::STATUS_APPROVED,
                        'admin_approved' => true,
                        'dispute_status' => JobSubmission::DISPUTE_NONE,
                        'is_frozen' => false
                    ]);
                }
            }
        }

        // create specific dispute scenarios for testing
        $this->createSpecificDisputeScenarios($jobs, $users);
    }

    /**
     * create specific dispute scenarios for comprehensive testing
     */
    private function createSpecificDisputeScenarios($jobs, $users): void
    {
        $adminUser = User::where('role', 'admin')->first();

        // scenario 1: Worker timeout dispute (48+ hours inactive)
        if ($jobs->count() >= 1 && $users->count() >= 2) {
            $job = $jobs->first();
            $worker = $users->where('id', '!=', $job->user_id)->first();
            
            JobSubmission::factory()->create([
                'job_listing_id' => $job->id,
                'user_id' => $worker->id,
                'status' => JobSubmission::STATUS_CLAIMED,
                'created_at' => now()->subDays(3), // 3 days old
                'updated_at' => now()->subDays(3),
                'dispute_status' => JobSubmission::DISPUTE_REQUESTED,
                'dispute_reason' => 'Worker has been inactive for over 48 hours after claiming the job',
                'dispute_initiated_by' => $job->user_id,
                'is_frozen' => true,
                'freeze_reason' => 'Automatically frozen due to worker inactivity timeout'
            ]);
        }
    }
}
