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

        // create additional static user accounts
        $staticUsers = [
            [
                'email' => 'user1@timerr.lat',
                'first_name' => 'Jānis',
                'last_name' => 'Bērziņš',
                'password' => 'user1',
                'time_credits' => 75,
            ],
            [
                'email' => 'user2@timerr.lat',
                'first_name' => 'Māra',
                'last_name' => 'Kalniņa',
                'password' => 'user2',
                'time_credits' => 120,
            ],
            [
                'email' => 'user3@timerr.lat',
                'first_name' => 'Andris',
                'last_name' => 'Ozoliņš',
                'password' => 'user3',
                'time_credits' => 50,
            ],
            [
                'email' => 'user4@timerr.lat',
                'first_name' => 'Līga',
                'last_name' => 'Liepiņa',
                'password' => 'user4',
                'time_credits' => 90,
            ],
            [
                'email' => 'user5@timerr.lat',
                'first_name' => 'Guntis',
                'last_name' => 'Krūmiņš',
                'password' => 'user5',
                'time_credits' => 60,
            ],
        ];

        foreach ($staticUsers as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                [
                    'first_name' => $userData['first_name'],
                    'last_name' => $userData['last_name'],
                    'email' => $userData['email'],
                    'password' => Hash::make($userData['password']),
                    'role' => 'user',
                    'time_credits' => $userData['time_credits'],
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
