<?php

namespace App\Providers;

use App\Sports\JudoProfile;
use App\Sports\KarateProfile;
use App\Sports\SportProfile;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Aktivni sport profil je fiksan po deploymentu (config('club.sport')).
        // Novi sport = novi case ovdje + novi profil u App\Sports.
        $this->app->singleton(SportProfile::class, function (): SportProfile {
            return match (config('club.sport')) {
                'karate' => new KarateProfile,
                'judo' => new JudoProfile,
                default => throw new InvalidArgumentException(
                    'Nepoznat sport u config(club.sport): '.(string) config('club.sport'),
                ),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
