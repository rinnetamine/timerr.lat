<?php

// Šis fails nosaka darba sludinājuma labošanas piekļuves noteikumus.

namespace App\Policies;

use App\Models\Job;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class JobPolicy
{
    // Pārbauda, vai lietotājs drīkst labot konkrēto darbu.
    public function edit(User $user, Job $job): bool
    {
        // Sludinājumu drīkst labot tikai tā īpašnieks.
        return $job->user->is($user);
    }
}
