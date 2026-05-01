<?php

// Šis fails ģenerē testa atsauksmes starp lietotājiem.

namespace Database\Factories;

use App\Models\JobSubmission;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReviewFactory extends Factory
{
    protected $model = Review::class;

    // Atgriež noklusējuma atsauksmes datus testiem un sēklu datiem.
    public function definition(): array
    {
        return [
            'job_submission_id' => JobSubmission::factory(),
            'reviewer_id' => User::factory(),
            'reviewee_id' => User::factory(),
            'rating' => fake()->numberBetween(1, 5),
            'comment' => fake('lv_LV')->optional(0.8)->paragraph(3), // 80% iespēja, ka atsauksmei būs komentārs.
        ];
    }
}
