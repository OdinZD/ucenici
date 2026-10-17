<?php

declare(strict_types=1);

return [

    /*
    | Aktivni sport za ovaj deployment. Bira sport profil iz App\Sports
    | (karate | judo | ...). Svaki deployment poslužuje jedan klub/sport.
    */

    'sport' => env('CLUB_SPORT', 'karate'),

    /*
    | Ime kluba — prikazuje se u zaglavlju stranice te u zaglavljima PDF
    | izvješća i Excel izvoza.
    */

    'name' => env('CLUB_NAME', 'Karate klub Optimist'),

    /*
    | Logo kluba za zaglavlje PDF izvješća, relativno na public/. Prikazuje se
    | samo ako datoteka postoji (inače se preskače). Svaki deployment postavi
    | svoju datoteku na ovu putanju ili pokaže CLUB_LOGO na drugu.
    */

    'logo' => env('CLUB_LOGO', 'images/club-logo.png'),

    /*
    | Treneri koji se smiju prijaviti (SPEC §1 — bez samo-registracije).
    | Comma-separated emailovi + jedna zajednička početna lozinka, oboje iz
    | okoline da se tajna ne commita. Koristi ih TrainerSeeder.
    */

    'trainer_emails' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('CLUB_TRAINER_EMAILS', '')),
    ))),

    'default_password' => env('CLUB_DEFAULT_PASSWORD'),

];
