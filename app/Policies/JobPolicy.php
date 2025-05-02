<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPolicy
{
    // determine if the user can edit the job
    public function edit(User $user, Job $job): bool
    {
        // only allow editing if the user is the job owner
        return $job->user->is($user);
    }
}
