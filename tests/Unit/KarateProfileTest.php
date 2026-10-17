<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Sports\AgeBand;
use App\Sports\Discipline;
use App\Sports\Grade;
use App\Sports\KarateProfile;
use PHPUnit\Framework\TestCase;

/**
 * Paritet: KarateProfile mora verbatim reproducirati izvorni hardkodirani karate
 * (bivši Belt / Discipline / AgeCategory::TIERS / tokens.css). Asertira se protiv
 * eksplicitnih literala pa ostaje čuvar i nakon uklanjanja starih enuma.
 */
final class KarateProfileTest extends TestCase
{
    private KarateProfile $profile;

    protected function setUp(): void
    {
        parent::setUp();
        $this->profile = new KarateProfile;
    }

    public function test_key(): void
    {
        $this->assertSame('karate', $this->profile->key());
    }

    public function test_grades_match_original_belt_enum(): void
    {
        $expected = [
            // code, label, colorPrimary, colorSecondary, isNone
            ['9', '9. kyu · bijeložuti', '#FFFFFF', '#F2D13B', false],
            ['8', '8. kyu · žuti', '#F2C230', null, false],
            ['7', '7. kyu · narančasti', '#EE8A2B', null, false],
            ['6', '6. kyu · crveni', '#D0342C', null, false],
            ['5', '5. kyu · zeleni', '#3C9A4B', null, false],
            ['4', '4. kyu · plavi', '#2F6FD0', null, false],
            ['3', '3. kyu · ljubičasti', '#7A4BB5', null, false],
            ['2', '2. kyu · smeđi', '#7A4A2B', null, false],
            ['1', '1. kyu · smeđe crni', '#7A4A2B', '#14171F', false],
            ['0', 'Bez pojasa', null, null, true],
        ];

        $actual = array_map(
            fn (Grade $g): array => [$g->code, $g->label, $g->colorPrimary, $g->colorSecondary, $g->isNone],
            $this->profile->grades(),
        );

        $this->assertSame($expected, $actual);
    }

    public function test_split_belts_are_kyu9_and_kyu1(): void
    {
        $this->assertTrue($this->profile->grade('9')?->isSplit());
        $this->assertTrue($this->profile->grade('1')?->isSplit());
        $this->assertFalse($this->profile->grade('6')?->isSplit());
        $this->assertFalse($this->profile->grade('0')?->isSplit());
    }

    public function test_grade_lookup(): void
    {
        $this->assertSame('6. kyu · crveni', $this->profile->grade('6')?->label);
        $this->assertNull($this->profile->grade(null));
        $this->assertNull($this->profile->grade('nema-takvog'));
    }

    public function test_grade_not_entered_label(): void
    {
        $this->assertSame('Nije uneseno', $this->profile->gradeNotEnteredLabel());
    }

    public function test_age_bands_match_original_tiers(): void
    {
        $expected = [
            ['ispod', 'Ispod U8', 6, 72],
            ['u08', 'U08 · Cicibani', 8, 96],
            ['u10', 'U10 · Mlađi učenici', 10, 120],
            ['u12', 'U12 · Učenici', 12, 144],
            ['u14', 'U14 · Mlađi kadeti', 14, 168],
            ['u16', 'U16 · Kadeti', 16, 192],
            ['u18', 'U18 · Juniori', 18, 216],
            ['u21', 'U21', 21, 252],
            ['sen', 'Seniori', PHP_INT_MAX, 0],
        ];

        $actual = array_map(
            fn (AgeBand $b): array => [$b->key, $b->label, $b->maxAge, $b->crossoverMonths],
            $this->profile->ageBands(),
        );

        $this->assertSame($expected, $actual);
    }

    public function test_disciplines_match_original_enum(): void
    {
        $expected = [
            ['kate', 'Kate'],
            ['kumite', 'Kumite'],
            ['kata_team', 'Kata team'],
        ];

        $actual = array_map(
            fn (Discipline $d): array => [$d->value, $d->label],
            $this->profile->disciplines(),
        );

        $this->assertSame($expected, $actual);
    }

    public function test_season_window(): void
    {
        $season = $this->profile->season();

        $this->assertSame([9, 1, 7, 1], [
            $season->startMonth, $season->startDay, $season->endMonth, $season->endDay,
        ]);
    }

    public function test_features(): void
    {
        $this->assertTrue($this->profile->feature('kata_teams'));
        $this->assertTrue($this->profile->feature('competitions'));
        $this->assertTrue($this->profile->feature('medical'));
        $this->assertTrue($this->profile->feature('declarations'));
        $this->assertFalse($this->profile->feature('nepostojeca'));
    }
}
