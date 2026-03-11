<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;

class JobSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = config('job_categories', []);

        foreach ($categories as $topKey => $group) {
            // Ensure at least 2 jobs for the top-level category (including one example)
            $randUser = User::inRandomOrder()->first();
            Job::factory()->forCategory($topKey)->count(1)->create(['user_id' => $randUser->id]); // example
            $randUser = User::inRandomOrder()->first();
            Job::factory()->forCategory($topKey)->count(1)->create(['user_id' => $randUser->id]); // ensure minimum 2

            // Ensure at least 2 jobs for each subcategory if any
            if (!empty($group['children']) && is_array($group['children'])) {
                foreach ($group['children'] as $slug => $label) {
                    $randUser = User::inRandomOrder()->first();
                    Job::factory()->forCategory($slug)->count(2)->create(['user_id' => $randUser->id]);
                }
            }
        }
    }
}
