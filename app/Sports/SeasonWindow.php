<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * The sport's season window as month/day pairs (start inclusive, end exclusive).
 *
 * Karate: 1.9. → 1.7. iduće godine (srpanj/kolovoz su izvan sezone).
 * App\Support\Season koristi ove granice; izračun godina/sadržavanja ostaje tamo.
 */
final readonly class SeasonWindow
{
    public function __construct(
        public int $startMonth,
        public int $startDay,
        public int $endMonth,
        public int $endDay,
    ) {}
}
