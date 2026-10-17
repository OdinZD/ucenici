<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Medal;
use App\Sports\Sport;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class CompetitionResult extends Model
{
    protected $fillable = [
        'student_id',
        'competition_id',
        'discipline',
        'category',
        'place',
        'wins',
        'losses',
        'bouts',
        'note',
    ];

    protected function casts(): array
    {
        return [
            // discipline is a plain string code; its label comes from the active
            // sport profile (disciplines vary per sport) — see disciplineLabel().
            'place' => 'integer',
            'wins' => 'integer',
            'losses' => 'integer',
            'bouts' => 'integer',
        ];
    }

    /** Display label for the stored discipline code, via the active sport profile. */
    public function disciplineLabel(): string
    {
        foreach (Sport::active()->disciplines() as $discipline) {
            if ($discipline->value === $this->discipline) {
                return $discipline->label;
            }
        }

        return (string) $this->discipline;
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function competition(): BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    /** @return Attribute<?Medal, never> */
    protected function medal(): Attribute
    {
        return Attribute::get(fn (): ?Medal => Medal::fromPlace($this->place));
    }
}
