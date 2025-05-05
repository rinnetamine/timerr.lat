<?php

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPolicy
{
    // check if user can edit a job
    public function edit(User $user, Job $job): bool
    {
        // verify user is the job owner
        return $job->user->is($user);
    }
}
