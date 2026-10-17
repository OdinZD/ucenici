@props(['grade' => null, 'showLabel' => true])

@php
    /** @var \App\Sports\Grade|null $grade */
    $label = $grade?->label ?? \App\Sports\Sport::active()->gradeNotEnteredLabel();

    // Stripe fill + border from the Grade (colours per sport profile, inline hex).
    [$background, $border] = match (true) {
        $grade === null   => ['var(--surface)', '1px dashed var(--belt-none-border)'],
        $grade->isNone    => ['var(--surface)', '1px solid var(--belt-none-border)'],
        $grade->isSplit() => ['linear-gradient(90deg, '.$grade->colorPrimary.' 50%, '.$grade->colorSecondary.' 50%)', '1px solid var(--belt-border)'],
        default           => [$grade->colorPrimary, '1px solid var(--belt-border)'],
    };
@endphp

<span class="inline-flex items-center gap-2">
    <span class="inline-block shrink-0" aria-hidden="true"
          style="width:30px;height:10px;border-radius:3px;background:{{ $background }};border:{{ $border }}"></span>
    @if ($showLabel)
        <span class="text-[14px] {{ $grade === null ? 'text-[var(--ink3)]' : 'text-[var(--ink)]' }}">{{ $label }}</span>
    @endif
</span>
