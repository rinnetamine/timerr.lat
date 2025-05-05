<?php

namespace App\Providers;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    // bootstrap application services
    public function boot(): void
    {
        // prevent lazy loading of models
        Model::preventLazyLoading();

        // define gates for authorization
        Gate::define('edit-job', function (User $user, Job $job) {
            // allow admins to edit any job
            if ($user->role === 'admin') {
                return true;
            }
            
            // allow users to edit their own jobs
            return $user->id === $job->user_id;
        });
    }
}
