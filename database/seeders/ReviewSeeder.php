<?php

namespace Database\Seeders;

use App\Models\JobSubmission;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // get approved job submissions (these are the ones that can be reviewed)
        $approvedSubmissions = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)
                                            ->with(['jobListing.user', 'user'])
                                            ->get();
        $users = User::all();

        foreach ($approvedSubmissions as $submission) {
            if (rand(1, 100) <= 90) {
                // check if review already exists for this submission
                if (!Review::where('job_submission_id', $submission->id)->exists()) {
                    // the reviewer should be the job owner (who created the job)
                    $jobOwner = $submission->jobListing->user;
                    
                    // don't review if the job owner is the same as the submission user
                    if ($jobOwner->id !== $submission->user_id) {
                        Review::factory()->create([
                            'job_submission_id' => $submission->id,
                            'reviewer_id' => $jobOwner->id,
                            'reviewee_id' => $submission->user_id,
                        ]);
                    }
                }
            }
        }

        // Ensure we have some reviews for testing - increased from 10 to 25
        $reviewCount = Review::count();
        if ($reviewCount < 25) {
            // Create additional reviews if we don't have enough
            $submissions = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)
                                       ->with(['jobListing.user', 'user'])
                                       ->take(25)
                                       ->get();
            
            foreach ($submissions as $submission) {
                $jobOwner = $submission->jobListing->user;
                
                if ($jobOwner->id !== $submission->user_id && !Review::where('job_submission_id', $submission->id)->exists()) {
                    Review::factory()->create([
                        'job_submission_id' => $submission->id,
                        'reviewer_id' => $jobOwner->id,
                        'reviewee_id' => $submission->user_id,
                    ]);
                }
            }
        }

        // Create some additional random reviews for variety - increased from 5 to 15
        $additionalReviews = 15;
        for ($i = 0; $i < $additionalReviews; $i++) {
            $submission = JobSubmission::where('status', JobSubmission::STATUS_APPROVED)
                                     ->with(['jobListing.user', 'user'])
                                     ->inRandomOrder()
                                     ->first();
            
            if ($submission) {
                $jobOwner = $submission->jobListing->user;
                
                if ($jobOwner->id !== $submission->user_id && !Review::where('job_submission_id', $submission->id)->exists()) {
                    Review::factory()->create([
                        'job_submission_id' => $submission->id,
                        'reviewer_id' => $jobOwner->id,
                        'reviewee_id' => $submission->user_id,
                    ]);
                }
            }
        }
    }
}
