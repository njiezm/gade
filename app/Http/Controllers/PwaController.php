<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

/**
 * PWA : service worker (/sw.js) et page hors connexion (/hors-ligne).
 * Le SW est généré à partir du manifeste Vite pour pré-cacher les fichiers hachés du build.
 */
class PwaController extends Controller
{
    public function serviceWorker(): Response
    {
        $manifeste = public_path('build/manifest.json');
        $build = is_file($manifeste) ? (json_decode(file_get_contents($manifeste), true) ?: []) : [];

        $fichiers = [];
        foreach ($build as $entree) {
            foreach (array_merge([$entree['file'] ?? null], $entree['css'] ?? [], $entree['assets'] ?? []) as $f) {
                if ($f) {
                    $fichiers['/build/'.$f] = true;
                }
            }
        }

        $shell = array_merge(array_keys($fichiers), [
            '/hors-ligne',
            '/manifest.webmanifest',
            '/images/brand/logo-gade.svg',
            '/images/brand/signe-gade.svg',
            '/images/brand/favicon.svg',
            '/images/pwa/icone-192.png',
            '/images/pwa/icone-512.png',
        ]);

        // La version change dès que le build ou le modèle du SW change : les anciens caches sont purgés.
        $version = substr(md5(json_encode($shell).(is_file($manifeste) ? filemtime($manifeste) : '')
            .filemtime(resource_path('views/pwa/sw.blade.php'))
            .filemtime(resource_path('views/pwa/hors-ligne.blade.php'))), 0, 10);

        return response()
            ->view('pwa.sw', ['shell' => $shell, 'version' => $version])
            ->header('Content-Type', 'application/javascript; charset=utf-8')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Service-Worker-Allowed', '/');
    }

    public function horsLigne(): Response
    {
        // Page neutre (sans profil ni menu personnalisé) : elle est mise en cache par le SW.
        return response()->view('pwa.hors-ligne')->header('Cache-Control', 'no-cache');
    }
}
