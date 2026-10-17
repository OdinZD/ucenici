<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * Judo sport profil.
 *
 * Značajke su postavljene (kata timovi isključeni), ali pojasevi, dobne skupine
 * i discipline su PLACEHOLDERI dok korisnik ne dostavi autoritativne judo podatke
 * (vidi docs/sql/ "AWAITING JUDO DATA" + plan). Prazne liste znače da judo
 * deployment još NIJE lansabilan — JudoProfileTest to izričito čuva.
 */
final class JudoProfile extends BaseSportProfile
{
    public function key(): string
    {
        return 'judo';
    }

    public function grades(): array
    {
        // AWAITING AUTHORITATIVE JUDO DATA FROM USER:
        // uređena lista pojaseva (code/label/redoslijed/colorPrimary/colorSecondary).
        return [];
    }

    public function gradeNotEnteredLabel(): string
    {
        return 'Nije uneseno';
    }

    public function ageBands(): array
    {
        // AWAITING AUTHORITATIVE JUDO DATA FROM USER: dobne skupine (judo savez).
        return [];
    }

    public function disciplines(): array
    {
        // AWAITING AUTHORITATIVE JUDO DATA FROM USER: discipline natjecanja
        // (ili [] ako judo klub ne koristi grupiranje po disciplini).
        return [];
    }

    public function season(): SeasonWindow
    {
        // Default na karate prozor dok se judo sezona ne potvrdi.
        return new SeasonWindow(9, 1, 7, 1);
    }

    protected function featureOverrides(): array
    {
        return ['kata_teams' => false];
    }
}
