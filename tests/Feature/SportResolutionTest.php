<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Sports\JudoProfile;
use App\Sports\KarateProfile;
use App\Sports\Sport;
use App\Sports\SportProfile;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * config('club.sport') mora razriješiti točan profil (singleton po deploymentu),
 * a nepoznat sport mora glasno pući umjesto tihog krivog ponašanja.
 */
final class SportResolutionTest extends TestCase
{
    private function resolveFor(string $sport): SportProfile
    {
        config(['club.sport' => $sport]);
        $this->app->forgetInstance(SportProfile::class);

        return Sport::active();
    }

    public function test_karate_resolves_to_karate_profile(): void
    {
        $this->assertInstanceOf(KarateProfile::class, $this->resolveFor('karate'));
    }

    public function test_judo_resolves_to_judo_profile(): void
    {
        $this->assertInstanceOf(JudoProfile::class, $this->resolveFor('judo'));
    }

    public function test_unknown_sport_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->resolveFor('kickboxing');
    }

    public function test_has_feature_sugar_reads_active_profile(): void
    {
        $this->resolveFor('judo');
        $this->assertFalse(Sport::hasFeature('kata_teams'));

        $this->resolveFor('karate');
        $this->assertTrue(Sport::hasFeature('kata_teams'));
    }
}
