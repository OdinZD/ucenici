<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Sports\KarateProfile;
use Illuminate\Testing\TestView;
use Tests\TestCase;

/**
 * The belt-stripe component must render each karate Grade exactly as the original
 * CSS-token version did: split gradients for kyu 9 & 1, solid fill for the middles,
 * neutral solid border for "no belt", dashed border for "not entered".
 */
final class BeltStripeTest extends TestCase
{
    private function render(?string $code): TestView
    {
        $grade = $code === null ? null : (new KarateProfile)->grade($code);

        return $this->blade('<x-belt-stripe :grade="$grade" />', ['grade' => $grade]);
    }

    public function test_not_entered_is_dashed_neutral(): void
    {
        $view = $this->render(null);
        $view->assertSee('1px dashed var(--belt-none-border)', false);
        $view->assertSee('background:var(--surface)', false);
        $view->assertSee('Nije uneseno');
    }

    public function test_no_belt_is_solid_neutral(): void
    {
        $view = $this->render('0');
        $view->assertSee('1px solid var(--belt-none-border)', false);
        $view->assertSee('background:var(--surface)', false);
        $view->assertSee('Bez pojasa');
    }

    public function test_kyu9_is_a_white_yellow_split(): void
    {
        $view = $this->render('9');
        $view->assertSee('linear-gradient(90deg, #FFFFFF 50%, #F2D13B 50%)', false);
        $view->assertSee('1px solid var(--belt-border)', false);
        $view->assertSee('9. kyu · bijeložuti');
    }

    public function test_kyu1_is_a_brown_black_split(): void
    {
        $view = $this->render('1');
        $view->assertSee('linear-gradient(90deg, #7A4A2B 50%, #14171F 50%)', false);
        $view->assertSee('1. kyu · smeđe crni');
    }

    public function test_middle_kyu_is_a_single_colour(): void
    {
        $view = $this->render('6');
        $view->assertSee('background:#D0342C', false);
        $view->assertSee('1px solid var(--belt-border)', false);
        $view->assertSee('6. kyu · crveni');
    }
}
