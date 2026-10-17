<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Livewire\StudentForm;
use App\Models\User;
use App\Sports\SportProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\RouteCollection;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Per-sport feature gating. Karate has kata teams; judo does not — under judo the
 * route must 404, the nav link and the drawer section must disappear.
 */
final class FeatureGatingTest extends TestCase
{
    use RefreshDatabase;

    /** Re-resolve the active profile and re-register web routes under a sport. */
    private function bootSport(string $sport): void
    {
        config(['club.sport' => $sport]);
        $this->app->forgetInstance(SportProfile::class);

        $this->app['router']->setRoutes(new RouteCollection);
        require base_path('routes/web.php');
        $this->app['router']->getRoutes()->refreshNameLookups();
    }

    public function test_karate_has_kata_teams_route_nav_and_form_section(): void
    {
        $user = User::factory()->create();

        $this->assertTrue(Route::has('kata-timovi'));
        $this->actingAs($user)->get('/polaznici')->assertOk()->assertSee('Kata timovi');

        Livewire::actingAs($user)->test(StudentForm::class)
            ->call('openCreate')
            ->assertSee('Kata timovi');
    }

    public function test_judo_hides_kata_teams_everywhere(): void
    {
        $this->bootSport('judo');

        $user = User::factory()->create();

        $this->assertFalse(Route::has('kata-timovi'));
        $this->actingAs($user)->get('/kata-timovi')->assertNotFound();

        $this->actingAs($user)->get('/polaznici')->assertOk()->assertDontSee('Kata timovi');

        Livewire::actingAs($user)->test(StudentForm::class)
            ->call('openCreate')
            ->assertDontSee('Kata timovi');
    }
}
