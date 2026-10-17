<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

/**
 * PWA: the manifest is served dynamically so each club's installed app shows its own
 * name (config('club.name')); the install plumbing is wired into the guest/app layouts.
 */
final class PwaTest extends TestCase
{
    public function test_manifest_route_is_public_and_club_scoped(): void
    {
        $this->get('/manifest.webmanifest')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJson([
                'name' => config('club.name'),
                'display' => 'standalone',
                'start_url' => '/polaznici',
            ]);
    }

    public function test_login_page_wires_up_the_pwa(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('/manifest.webmanifest', false)
            ->assertSee('beforeinstallprompt', false);
    }
}
