<?php

// Šis fails inicializē lietotnes kopīgos iestatījumus un autorizācijas vārtus.

namespace App\Providers;

use App\Models\Job;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // Reģistrē lietotnes pakalpojumus, ja tiem vēlāk nepieciešama papildu konfigurācija.
    public function register(): void
    {
    }

    // Sākot lietotni, iestata lokalizāciju, modeļu uzvedību un autorizācijas vārtus.
    public function boot(): void
    {
        app()->setLocale(config('app.locale'));
        Carbon::setLocale(config('app.locale'));

        // Slinkās ielādes aizliegums palīdz ātrāk pamanīt nepietiekami definētas attiecības.
        Model::preventLazyLoading();

        // Vārti centralizē darba labošanas tiesības.
        Gate::define('edit-job', function (User $user, Job $job) {
            // Administrators drīkst labot jebkuru sludinājumu.
            if ($user->role === 'admin') {
                return true;
            }
            
            // Parasts lietotājs drīkst labot tikai savus sludinājumus.
            return $user->id === $job->user_id;
        });
    }
}
