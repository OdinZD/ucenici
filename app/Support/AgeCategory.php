<?php

declare(strict_types=1);

namespace App\Support;

use App\Sports\Sport;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use LogicException;

/**
 * The student's age category and the date they cross into the next one.
 *
 * Bands come from the active sport profile (App\Sports); karate reproduces the
 * WKF/HKS table. Never stored — always derived from birth_date and "today"
 * (SPEC §4.1). Logic mirrors the approved prototype: age is an explicit integer
 * (not Carbon's float diffInYears) and the crossover uses EDATE-style
 * no-overflow month arithmetic.
 */
final class AgeCategory
{
    private function __construct(
        public readonly string $key,
        public readonly string $label,
        public readonly int $age,
        public readonly ?CarbonImmutable $crossover,
    ) {}

    /** Null birth date => no category (filter "Bez datuma rođenja"). */
    public static function for(?CarbonInterface $birthDate, CarbonInterface $today): ?self
    {
        if ($birthDate === null) {
            return null;
        }

        $age = self::ageInYears($birthDate, $today);

        foreach (Sport::active()->ageBands() as $band) {
            if ($age < $band->maxAge) {
                $crossover = $band->crossoverMonths > 0
                    ? CarbonImmutable::instance($birthDate)->addMonthsNoOverflow($band->crossoverMonths)
                    : null;

                return new self($band->key, $band->label, $age, $crossover);
            }
        }

        throw new LogicException('No age band matched; the final band should be unbounded.');
    }

    /** Completed years, counting a birthday today as already reached. */
    private static function ageInYears(CarbonInterface $birthDate, CarbonInterface $today): int
    {
        $age = $today->year - $birthDate->year;

        $birthdayNotYetReached = $today->month < $birthDate->month
            || ($today->month === $birthDate->month && $today->day < $birthDate->day);

        return $birthdayNotYetReached ? $age - 1 : $age;
    }
}
