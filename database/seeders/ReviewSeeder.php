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
        // create just a couple of sample reviews for testing
        $users = User::all();
        
        if ($users->count() >= 3) {
            for ($i = 0; $i < 3; $i++) {
                $reviewer = $users->random();
                $reviewee = $users->where('id', '!=', $reviewer->id)->random();
                
                Review::factory()->create([
                    'reviewer_id' => $reviewer->id,
                    'reviewee_id' => $reviewee->id,
                    'job_submission_id' => null, 
                ]);
            }
        }
    }
}
