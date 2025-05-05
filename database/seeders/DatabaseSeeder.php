<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Job;
use App\Models\JobSubmission;
use App\Models\SubmissionFile;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create users
        $users = User::factory(10)->create();

        // Create job listings with relationships
        $jobs = Job::factory(20)
            ->for($users->random())
            ->create();
    }
}
