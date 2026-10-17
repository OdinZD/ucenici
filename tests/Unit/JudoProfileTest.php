<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Sports\JudoProfile;
use PHPUnit\Framework\TestCase;

/**
 * Judo značajke su postavljene (kata timovi off, ostalo on), ali pojasevi/skupine/
 * discipline su namjerno prazni placeholderi dok ne stignu autoritativni podaci.
 * Ovaj test čuva da se prazan judo ne pusti slučajno kao gotov.
 */
final class JudoProfileTest extends TestCase
{
    private JudoProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profile = new JudoProfile;
    }

    public function test_key(): void
    {
        $this->assertSame('judo', $this->profile->key());
    }

    public function test_kata_teams_disabled_other_features_enabled(): void
    {
        $this->assertFalse($this->profile->feature('kata_teams'));
        $this->assertTrue($this->profile->feature('competitions'));
        $this->assertTrue($this->profile->feature('medical'));
        $this->assertTrue($this->profile->feature('declarations'));
    }

    public function test_sport_data_is_placeholder_until_authoritative_input(): void
    {
        // Namjerno prazno — judo deployment je blokiran dok se ne popuni.
        $this->assertSame([], $this->profile->grades());
        $this->assertSame([], $this->profile->ageBands());
        $this->assertSame([], $this->profile->disciplines());
    }
}
