<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // number of users to create; configurable via env for convenience
        $count = (int) env('SEED_USERS', 30);

        // create a mix of users
        User::factory()->count($count)->create();

        // ensure a demo/admin user exists to login quickly
        User::firstOrCreate(
            ['email' => 'demo@local.test'],
            [
                'first_name' => 'Demo',
                'last_name' => 'User',
                'password' => 'password', // factory will hash if model casts apply
                'time_credits' => 50,
                'role' => 'admin'
            ]
        );
    }
}
