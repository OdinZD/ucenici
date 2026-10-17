<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * Zajednička logika za sport profile: značajke (default + override) i lookup
 * pojasa po kodu. Konkretni profili deklariraju samo razlike.
 */
abstract class BaseSportProfile implements SportProfile
{
    /**
     * Značajke zajedničke svim sportovima osim ako ih profil ne pregazi.
     *
     * @return array<string, bool>
     */
    protected function featureDefaults(): array
    {
        return [
            'competitions' => true,
            'medical' => true,
            'declarations' => true,
            'kata_teams' => false,
        ];
    }

    /**
     * Odstupanja ovog sporta od defaulta.
     *
     * @return array<string, bool>
     */
    abstract protected function featureOverrides(): array;

    public function features(): array
    {
        return array_merge($this->featureDefaults(), $this->featureOverrides());
    }

    public function feature(string $key): bool
    {
        return $this->features()[$key] ?? false;
    }

    public function grade(?string $code): ?Grade
    {
        if ($code === null) {
            return null;
        }

        foreach ($this->grades() as $grade) {
            if ($grade->code === $code) {
                return $grade;
            }
        }

        return null;
    }

    public function suggestNextGradeCode(?string $currentCode): ?string
    {
        // Registrabilni pojasevi (bez "none") u redoslijedu napredovanja = redoslijed grades().
        $registerable = array_values(array_filter(
            $this->grades(),
            fn (Grade $grade): bool => ! $grade->isNone,
        ));

        if ($registerable === []) {
            return null;
        }

        $current = $this->grade($currentCode);

        if ($current === null || $current->isNone) {
            return $registerable[0]->code;
        }

        foreach ($registerable as $i => $grade) {
            if ($grade->code === $current->code) {
                return ($registerable[$i + 1] ?? $grade)->code;
            }
        }

        return $registerable[0]->code;
    }
}
