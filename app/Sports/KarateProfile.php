<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * Karate (WKF/HKS) sport profil.
 *
 * Vrijednosti su prenesene verbatim iz izvornog hardkodiranog karate koda
 * (bivši App\Enums\Belt, App\Enums\Discipline, App\Support\AgeCategory::TIERS i
 * resources/css/tokens.css), tako da je ponašanje za karate identično. Paritet
 * čuva KarateProfileTest.
 */
final class KarateProfile extends BaseSportProfile
{
    public function key(): string
    {
        return 'karate';
    }

    public function grades(): array
    {
        // Prikazni redoslijed 9 → 1 pa "Bez pojasa" (bivši Belt::options()).
        // Kodovi su numerički ('0'–'9') radi trivijalnog budućeg backfilla
        // (belt_code = CAST(belt_kyu AS CHAR)).
        return [
            new Grade('9', '9. kyu · bijeložuti', '#FFFFFF', '#F2D13B'),
            new Grade('8', '8. kyu · žuti', '#F2C230'),
            new Grade('7', '7. kyu · narančasti', '#EE8A2B'),
            new Grade('6', '6. kyu · crveni', '#D0342C'),
            new Grade('5', '5. kyu · zeleni', '#3C9A4B'),
            new Grade('4', '4. kyu · plavi', '#2F6FD0'),
            new Grade('3', '3. kyu · ljubičasti', '#7A4BB5'),
            new Grade('2', '2. kyu · smeđi', '#7A4A2B'),
            new Grade('1', '1. kyu · smeđe crni', '#7A4A2B', '#14171F'),
            new Grade('0', 'Bez pojasa', isNone: true),
        ];
    }

    public function gradeNotEnteredLabel(): string
    {
        return 'Nije uneseno';
    }

    public function ageBands(): array
    {
        return [
            new AgeBand('ispod', 'Ispod U8', 6, 72),
            new AgeBand('u08', 'U08 · Cicibani', 8, 96),
            new AgeBand('u10', 'U10 · Mlađi učenici', 10, 120),
            new AgeBand('u12', 'U12 · Učenici', 12, 144),
            new AgeBand('u14', 'U14 · Mlađi kadeti', 14, 168),
            new AgeBand('u16', 'U16 · Kadeti', 16, 192),
            new AgeBand('u18', 'U18 · Juniori', 18, 216),
            new AgeBand('u21', 'U21', 21, 252),
            new AgeBand('sen', 'Seniori', PHP_INT_MAX, 0),
        ];
    }

    public function disciplines(): array
    {
        return [
            new Discipline('kate', 'Kate'),
            new Discipline('kumite', 'Kumite'),
            new Discipline('kata_team', 'Kata team'),
        ];
    }

    public function season(): SeasonWindow
    {
        return new SeasonWindow(9, 1, 7, 1);
    }

    protected function featureOverrides(): array
    {
        return ['kata_teams' => true];
    }
}
