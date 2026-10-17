<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * One age band definition for a sport (karate WKF/HKS, judo, ...).
 *
 * Definicija je u sport profilu; izračun za pojedinog polaznika radi
 * App\Support\AgeCategory iz datuma rođenja (nikad se ne sprema, SPEC §4.1).
 */
final readonly class AgeBand
{
    public function __construct(
        public string $key,
        public string $label,
        public int $maxAge,          // gornja granica (isključivo); PHP_INT_MAX za otvorenu zadnju skupinu
        public int $crossoverMonths, // mjeseci od rođenja do prelaska; 0 = nema prelaska (zadnja skupina)
    ) {}
}
