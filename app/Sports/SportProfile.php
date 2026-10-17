<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * Sve što je specifično za jedan sport: hijerarhija pojaseva, dobne skupine,
 * discipline natjecanja, sezona i uključene značajke. Jedan profil je aktivan
 * po deploymentu (bira ga config('club.sport')); razrješava se kroz Sport::active().
 */
interface SportProfile
{
    /** Ključ sporta; podudara se s config('club.sport'). */
    public function key(): string;

    /**
     * Pojasevi u prikaznom redoslijedu (redoslijed <select>-a). Za registrabilne
     * (ne-"none") pojaseve taj redoslijed je ujedno redoslijed napredovanja.
     *
     * @return list<Grade>
     */
    public function grades(): array;

    /** Pojas po pohranjenom kodu; null za null/nepoznat kod. */
    public function grade(?string $code): ?Grade;

    /** Labela kad pojas nije unesen (null belt_code). */
    public function gradeNotEnteredLabel(): string;

    /**
     * Predloženi kod sljedećeg stupnja za upis, s obzirom na trenutni pojas
     * (SPEC §4.8): sljedeći registrabilni pojas u redoslijedu napredovanja;
     * bez pojasa/neuneseno → prvi; već na vrhu → ostaje. null ako nema pojaseva.
     */
    public function suggestNextGradeCode(?string $currentCode): ?string;

    /**
     * Dobne skupine od najmlađe prema starijoj; zadnja je neograničena.
     *
     * @return list<AgeBand>
     */
    public function ageBands(): array;

    /**
     * Discipline natjecanja; [] znači da sport ne grupira rezultate po disciplini.
     *
     * @return list<Discipline>
     */
    public function disciplines(): array;

    /** Granice sezone (mjesec/dan). */
    public function season(): SeasonWindow;

    /** Je li značajka uključena za ovaj sport (npr. 'kata_teams'). */
    public function feature(string $key): bool;

    /** @return array<string, bool> */
    public function features(): array;
}
