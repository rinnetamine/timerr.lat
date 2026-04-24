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
        // number of users to create; reduced for testing
        $count = (int) env('SEED_USERS', 5);

        // create a mix of users
        User::factory()->count($count)->create();

        // ensure a demo/admin user exists to login quickly
        User::firstOrCreate(
            ['email' => 'demo@local.test'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Lietotājs',
                'password' => 'password', // factory will hash if model casts apply
                'time_credits' => 50,
                'role' => 'admin'
            ]
        );
    }
}
