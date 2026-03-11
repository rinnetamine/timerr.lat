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
        // seed users and jobs; user seeder will create a demo/admin user too
        $this->call([
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\JobSeeder::class,
        ]);
    }
}
