<?php

declare(strict_types=1);

namespace App\Support;

use App\Sports\Sport;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * The club's season, as defined by the active sport profile (App\Sports). Default
 * (karate): 1 September → 1 July of the next year (July/August off-season). A season
 * is identified by its start year (the start-month year), e.g. 2025 = "2025/2026".
 * Assumes the window spans a calendar-year boundary (start autumn → end next summer).
 */
final class Season
{
    /** Season currently in progress on the given day. */
    public static function current(CarbonInterface $today): int
    {
        return $today->month >= Sport::active()->season()->startMonth ? $today->year : $today->year - 1;
    }

    /**
     * @return array{0: CarbonImmutable, 1: CarbonImmutable} [from inclusive, to exclusive]
     */
    public static function window(int $start): array
    {
        $window = Sport::active()->season();

        return [
            CarbonImmutable::create($start, $window->startMonth, $window->startDay, 0, 0, 0),
            CarbonImmutable::create($start + 1, $window->endMonth, $window->endDay, 0, 0, 0),
        ];
    }

    public static function contains(int $start, CarbonInterface $date): bool
    {
        [$from, $to] = self::window($start);

        return $date >= $from && $date < $to;
    }

    /** Human label, e.g. "2025/2026". */
    public static function label(int $start): string
    {
        return $start.'/'.($start + 1);
    }

    /** Filename-safe label, e.g. "2025-2026". */
    public static function slug(int $start): string
    {
        return $start.'-'.($start + 1);
    }
}
