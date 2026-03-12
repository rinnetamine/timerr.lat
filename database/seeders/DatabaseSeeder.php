<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

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
            \Database\Seeders\JobSubmissionSeeder::class,
            \Database\Seeders\ReviewSeeder::class,
        ]);
    }
}
