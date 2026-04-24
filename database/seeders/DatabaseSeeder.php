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
        // seed users and jobs only - no submissions or reviews
        $this->call([
            \Database\Seeders\AdminSeeder::class,
            \Database\Seeders\UserSeeder::class,
            \Database\Seeders\JobSeeder::class,
        ]);
    }

    // automatically run seeder when called from migration
    public function runWithDefaults(): void
    {
        $this->run();
    }
}
