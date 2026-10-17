<?php

declare(strict_types=1);

namespace App\Sports;

/**
 * Statički pristup aktivnom sport profilu. Profil je singleton vezan u
 * AppServiceProvider iz config('club.sport') (fiksan po deploymentu).
 */
final class Sport
{
    public static function active(): SportProfile
    {
        return app(SportProfile::class);
    }

    public static function hasFeature(string $key): bool
    {
        return self::active()->feature($key);
    }
}
