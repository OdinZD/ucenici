<?php

declare(strict_types=1);

use App\Http\Controllers\DeclarationController;
use App\Livewire\Auth\Login;
use App\Livewire\CompetitionsList;
use App\Livewire\KataTeamsList;
use App\Livewire\StudentsList;
use App\Sports\Sport;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/polaznici');

// Dinamički PWA manifest — ime se uzima iz config('club.name') pa svaki klub
// dobije svoj naziv instalirane aplikacije. Javno dostupno (bez auth).
Route::get('/manifest.webmanifest', function () {
    return response()->json([
        'name' => config('club.name'),
        'short_name' => config('club.name'),
        'lang' => 'hr',
        'id' => '/',
        'start_url' => '/polaznici',
        'scope' => '/',
        'display' => 'standalone',
        'background_color' => '#F4F5F7',
        'theme_color' => '#2D3E7B',
        'icons' => [
            ['src' => '/icons/icon-192.png', 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/icons/icon-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any'],
            ['src' => '/icons/icon-maskable-512.png', 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'maskable'],
        ],
    ])->header('Content-Type', 'application/manifest+json');
})->name('manifest');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', Login::class)->name('login');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/polaznici', StudentsList::class)->name('polaznici');
    Route::get('/natjecanja', CompetitionsList::class)->name('natjecanja');

    // Registrirana samo za sportove koji imaju kata timove (npr. karate; judo nema).
    if (Sport::hasFeature('kata_teams')) {
        Route::get('/kata-timovi', KataTeamsList::class)->name('kata-timovi');
    }

    Route::get('/izjava/{student}', DeclarationController::class)->name('izjava');

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
