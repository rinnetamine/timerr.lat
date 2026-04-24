<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\JobSubmission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class JobSubmissionFactory extends Factory
{
    protected $model = JobSubmission::class;

    public function definition(): array
    {
        return [
            'job_listing_id' => Job::factory(),
            'user_id' => User::factory(),
            'message' => fake('lv_LV')->paragraph(3),
            'status' => fake()->randomElement([
                JobSubmission::STATUS_CLAIMED,
                JobSubmission::STATUS_PENDING,
                JobSubmission::STATUS_APPROVED,
                JobSubmission::STATUS_DECLINED,
                JobSubmission::STATUS_ADMIN_REVIEW
            ]),
            'admin_notes' => fake('lv_LV')->optional(0.3)->paragraph(2),
            'admin_approved' => fake()->boolean(70), // 70% chance of being approved
        ];
    }
}
