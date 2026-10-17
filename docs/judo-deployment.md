# Judo (i budući sportovi) — deployment i konfiguracija

Ova baza koda je **višesportska**: jedan deployment poslužuje jedan klub/sport, biran configom
(`config/club.php` → `CLUB_SPORT`). Karate je jedan profil (`App\Sports\KarateProfile`), judo drugi
(`App\Sports\JudoProfile`). Izolacija podataka je **fizička** — svaki klub ima vlastitu bazu, vlastiti
`APP_KEY` i vlastiti cPanel site; nema `tenant_id` ni dijeljenja podataka (PII maloljetnika se ne miješa).

Pravila sporta (pojasevi, dobne skupine, discipline natjecanja, sezona, značajke) žive u kodu, u
`app/Sports/<Sport>Profile.php`. Novi sport = novi profil + jedan `case` u
`App\Providers\AppServiceProvider` (`match` na `config('club.sport')`).

---

## 1. Preduvjet — autoritativni judo podaci (bez njih judo NIJE lansabilan)

`JudoProfile` trenutno ima **placeholdere** (prazne liste); `JudoProfileTest` čuva da se takav ne pusti
kao gotov. Prije puštanja popuni `app/Sports/JudoProfile.php` sljedećim, provjerenim podacima:

1. **Pojasevi** — uređena lista (redoslijed napredovanja: od početnog prema naprednom), svaki:
   - `code` (stabilan string koji se sprema u `students.belt_code`, npr. `bijeli`, `zuti`, `k5`, `dan1`),
   - `label` (hrvatski, za prikaz),
   - `colorPrimary` (hex), opcionalno `colorSecondary` (hex, za prugaste/split pojaseve),
   - zadnji unos `new Grade('...', 'Bez pojasa', isNone: true)` ako sport ima „bez pojasa" baseline.
   - Potvrdi prati li klub i **dan** stupnjeve.
2. **Dobne skupine** — tablica kao karate (`new AgeBand(key, label, maxAge, crossoverMonths)`),
   od najmlađe prema starijoj; zadnja neograničena (`PHP_INT_MAX`, `crossoverMonths: 0`). Judo savez se
   razlikuje od WKF-a — mora biti autoritativno.
3. **Discipline natjecanja** — `new Discipline(value, label)` po disciplini; **ili ostavi `[]`** ako judo
   klub ne grupira rezultate po disciplini.
4. **Sezona** — `new SeasonWindow(startMonth, startDay, endMonth, endDay)`. Ako je ista kao karate
   (1.9.→1.7.), ostavi kako jest; inače prilagodi.

Sekundarno: `CLUB_NAME`, treneri (emailovi), te ostaju li liječnički/izjave uključeni (default: da).

---

## 2. Stand-up judo sitea na cPanelu (zaseban od karatea)

Mirrorira `docs/deploy-cpanel.md`, ali za **zasebnu** domenu + bazu + `.env`.

1. **Site:** nova (sub)domena, document root → `.../ucenici/public`.
2. **Git:** cPanel Git Version Control klon `OdinZD/ucenici`; deploy = `git pull` + „Update from Remote".
   `vendor/` je commitan (nema composera na produkciji — ali vidi napomenu o dev-ovisnostima niže).
3. **Baza:** nova MariaDB baza + korisnik + lozinka (vlastiti, ne dijeljeni s karateom).
4. **Produkcijski `.env`** (svjež, ne kopiraj karate):
   - `APP_KEY=` → generiraj lokalno (`php artisan key:generate --show`) i zalijepi (izjave su šifrirane
     ovim ključem — mora biti jedinstven po klubu),
   - `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://…`,
   - `CLUB_SPORT=judo`, `CLUB_NAME="<naziv judo kluba>"`,
   - `CLUB_TRAINER_EMAILS=…`, `CLUB_DEFAULT_PASSWORD=<jaka>`,
   - `DB_*` za novu bazu, `SESSION_SECURE_COOKIE=true`, locale `hr`.
5. **Shema:** `php artisan migrate --force` (terminal na hostu; isti put kao inicijalni karate deploy).
   Migracije već stvaraju `students.belt_code` / `next_grade_code` (string) i `competition_results.discipline`
   VARCHAR(30). Ako terminal nije dostupan: generiraj `php artisan schema:dump` lokalno na scratch bazi pa
   zalijepi u phpMyAdmin (SQL mirrori u `docs/sql/` su inkrementalne zakrpe, ne puna shema).
6. **Treneri:** `php artisan db:seed --class=TrainerSeeder --force` (ili ručni insert bcrypt hasheva).
7. **Prazni podaci:** NE kopiraj karate `database/seeders/data/polaznici.json`. Judo kreće s nula polaznika;
   `locations`/`training_groups` seedaj prema stvarnim judo lokacijama/grupama.
8. **Cache:** nakon svake `.env` promjene `php artisan config:cache route:cache view:cache`
   (rute se kešira po sportu — judo neće imati `/kata-timovi`).

Napomena o dev-ovisnostima: lokalni razvoj traži `composer install` (dev: phpunit/pint). Prije commita za
produkciju vrati `--no-dev`: `composer install --no-dev -o` (kao i dosad).

---

## 3. Ugovor za budući prelazak karatea na ovaj kod (integer → code)

Karate trenutno živi u zasebnom `optimiststudenti` repou s postojećom bazom (stupci `belt_kyu` TINYINT,
`next_grade_kyu` TINYINT). Ako se ikad prebaci na ovu bazu koda protiv **postojeće** karate baze, treba
jednokratna migracija podataka (kodovi `KarateProfile`-a su numerički baš radi ovoga):

```sql
ALTER TABLE students ADD COLUMN belt_code VARCHAR(255) NULL AFTER training_group_id;
ALTER TABLE students ADD COLUMN next_grade_code VARCHAR(255) NULL AFTER next_grade_status;
UPDATE students SET belt_code = CAST(belt_kyu AS CHAR) WHERE belt_kyu IS NOT NULL;
UPDATE students SET next_grade_code = CAST(next_grade_kyu AS CHAR) WHERE next_grade_kyu IS NOT NULL;
ALTER TABLE students DROP COLUMN belt_kyu, DROP COLUMN next_grade_kyu;
-- discipline: već VARCHAR; proširi ako je potrebno
ALTER TABLE competition_results MODIFY discipline VARCHAR(30) NOT NULL;
```

Mapiranje je 1:1: karate kodovi su `'9'`…`'1'` (kyu), `'0'` (bez pojasa), `NULL` (nije uneseno) —
identično starim `belt_kyu` vrijednostima.

---

## 4. Provjera (nakon što su judo podaci popunjeni)

- `php artisan test` zelen; `./vendor/bin/pint` čist.
- Lokalno s `CLUB_SPORT=judo` i svježom bazom: prijava → **nema** „Kata timovi" u navigaciji;
  `/kata-timovi` vraća 404; belt select u formi prikazuje **judo** pojaseve; chipovi uzrasta koriste
  **judo** dobne skupine; zaglavlja PDF/Excel izvoza pokazuju naziv judo kluba; popis je prazan
  (nema curenja karate polaznika).
- Karate (`optimiststudenti`) netaknut i dalje radi u svom repou.
