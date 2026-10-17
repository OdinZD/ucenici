<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * A competition discipline for a sport (karate: Kate/Kumite/Kata team; judo: ...).
 *
 * $value se pohranjuje u competition_results.discipline; $label je za prikaz.
 * Prazna lista disciplina u profilu znači da sport ne koristi grupiranje po
 * disciplini na natjecanjima.
 */
final readonly class Discipline
{
    public function __construct(
        public string $value,
        public string $label,
    ) {}
}
