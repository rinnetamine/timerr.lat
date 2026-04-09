<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{

    public function run(): void
    {
        // create or update admin account
        User::updateOrCreate(
            ['email' => 'admin@timerr.lat'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@timerr.lat',
                'password' => Hash::make('admin'),
                'role' => 'admin',
                'time_credits' => 1000,
                'email_verified_at' => now(),
            ]
        );

        // create or update static user account
        User::updateOrCreate(
            ['email' => 'user@timerr.lat'],
            [
                'first_name' => 'Test',
                'last_name' => 'User',
                'email' => 'user@timerr.lat',
                'password' => Hash::make('user'),
                'role' => 'user',
                'time_credits' => 100,
                'email_verified_at' => now(),
            ]
        );
    }
}
