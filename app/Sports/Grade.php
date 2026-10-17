<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * A single belt/grade in a sport's hierarchy.
 *
 * Pohranjuje se samo $code (students.belt_code / next_grade_code); sve ostalo
 * (labela, boje) razrješava aktivni sport profil. $code je neproziran string,
 * stabilan po sportu (karate koristi '0'–'9', drugi sportovi svoje kodove).
 */
final readonly class Grade
{
    public function __construct(
        public string $code,
        public string $label,
        public ?string $colorPrimary = null,   // hex; null kad je $isNone (nema ispune)
        public ?string $colorSecondary = null, // hex; != null => prugasti/split 50/50
        public bool $isNone = false,            // izričiti "bez pojasa" (rub, bez ispune)
    ) {}

    public function isSplit(): bool
    {
        return $this->colorSecondary !== null;
    }
}
