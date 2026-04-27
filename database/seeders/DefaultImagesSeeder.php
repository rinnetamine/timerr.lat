<?php

namespace Database\Seeders;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DefaultImagesSeeder extends Seeder
{
    public function run(): void
    {
        if (!Schema::hasColumn('users', 'avatar_path') || !Schema::hasColumn('job_listings', 'image_path')) {
            return;
        }

        User::query()
            ->whereNull('avatar_path')
            ->get()
            ->each(function (User $user) {
                $user->update([
                    'avatar_path' => User::defaultAvatarForSeed($user->email ?? $user->id),
                ]);
            });

        Job::query()
            ->whereNull('image_path')
            ->get()
            ->each(function (Job $job) {
                $job->update([
                    'image_path' => Job::defaultImagePathForCategory($job->category),
                ]);
            });
    }
}
