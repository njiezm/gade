<?php

namespace Tests\Feature;

use Tests\TestCase;

class PwaTest extends TestCase
{
    public function test_le_service_worker_est_servi_en_javascript_sans_cache(): void
    {
        $r = $this->get('/sw.js')->assertOk();

        $this->assertStringStartsWith('application/javascript', $r->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', $r->headers->get('Cache-Control'));
        $r->assertSee('SKIP_WAITING', false)
            ->assertSee('/hors-ligne', false)
            ->assertSee('gade-shell-', false);

        // Le pré-cache contient le CSS et le JS hachés du build Vite
        $build = json_decode(file_get_contents(public_path('build/manifest.json')), true);
        $r->assertSee(str_replace('/', '\/', '/build/'.$build['resources/js/app.js']['file']), false);
    }

    public function test_le_manifest_est_un_json_valide_et_installable(): void
    {
        $m = json_decode(file_get_contents(public_path('manifest.webmanifest')), true, flags: JSON_THROW_ON_ERROR);

        $this->assertSame('GADÉ', $m['short_name']);
        $this->assertSame('standalone', $m['display']);
        $this->assertSame('/', $m['start_url']);
        $this->assertSame('/', $m['scope']);
        $tailles = collect($m['icons'])->map(fn ($i) => $i['sizes'].'|'.($i['purpose'] ?? 'any'))->all();
        $this->assertContains('192x192|any', $tailles);
        $this->assertContains('512x512|any', $tailles);
        $this->assertContains('512x512|maskable', $tailles);
        foreach ($m['icons'] as $icone) {
            $this->assertFileExists(public_path(ltrim($icone['src'], '/')));
        }
    }

    public function test_la_page_hors_ligne_est_publique_et_neutre(): void
    {
        $this->get('/hors-ligne')->assertOk()
            ->assertSee('Pa ni rézo')
            ->assertSee('Il faut Internet pour regarder')
            ->assertDontSee('data-tv-interrupteur', false);
    }
}
