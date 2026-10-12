<?php

namespace App\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * Accès aux informations publiques de YouTube, sans clé d'API :
 *  - oEmbed (titre, chaîne, vignette) : un 401 ou un 404 = vidéo non intégrable ou supprimée ;
 *  - vignette i.ytimg.com téléchargée et convertie en WebP dans public/vignettes.
 * Aucune vidéo n'est téléchargée : la lecture passe toujours par le lecteur intégré youtube-nocookie.
 */
class YouTube
{
    /** Extrait l'identifiant (11 caractères) d'une URL YouTube ou d'un identifiant nu. */
    public static function idDepuisUrl(?string $url): ?string
    {
        $url = trim((string) $url);
        if (preg_match('/^[A-Za-z0-9_-]{11}$/', $url)) {
            return $url;
        }
        $motifs = [
            '~youtu\.be/([A-Za-z0-9_-]{11})~',
            '~[?&]v=([A-Za-z0-9_-]{11})~',
            '~youtube(?:-nocookie)?\.com/(?:embed|shorts|live|v)/([A-Za-z0-9_-]{11})~',
        ];
        foreach ($motifs as $m) {
            if (preg_match($m, $url, $r)) {
                return $r[1];
            }
        }

        return null;
    }

    /**
     * Interroge l'oEmbed public.
     *
     * @return array{statut:string, code:int, titre?:string, chaine_nom?:string, chaine_url?:string, vignette_url?:string}
     *         statut : ok | indisponible (401/403/404) | erreur (réseau, 5xx…)
     */
    public function oembed(string $id): array
    {
        try {
            $r = Http::timeout(15)->acceptJson()->get('https://www.youtube.com/oembed', [
                'url' => 'https://www.youtube.com/watch?v='.$id,
                'format' => 'json',
            ]);
        } catch (Throwable $e) {
            return ['statut' => 'erreur', 'code' => 0, 'message' => $e->getMessage()];
        }

        if ($r->successful()) {
            $j = $r->json();

            return [
                'statut' => 'ok',
                'code' => $r->status(),
                'titre' => (string) ($j['title'] ?? ''),
                'chaine_nom' => (string) ($j['author_name'] ?? ''),
                'chaine_url' => (string) ($j['author_url'] ?? ''),
                'vignette_url' => (string) ($j['thumbnail_url'] ?? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg"),
            ];
        }

        if (in_array($r->status(), [401, 403, 404], true)) {
            return ['statut' => 'indisponible', 'code' => $r->status()];
        }

        return ['statut' => 'erreur', 'code' => $r->status()];
    }

    /**
     * Télécharge la vignette hqdefault, retire les bandes noires (4:3 -> 16:9) et l'enregistre en WebP.
     * Retourne le chemin public (vignettes/ID.webp) ou null si impossible.
     */
    public function enregistrerVignette(string $id): ?string
    {
        try {
            $r = Http::timeout(15)->get("https://i.ytimg.com/vi/{$id}/hqdefault.jpg");
            if (! $r->successful() || ! function_exists('imagecreatefromstring')) {
                return null;
            }
            $src = @imagecreatefromstring($r->body());
            if (! $src) {
                return null;
            }
            $w = imagesx($src);
            $h = imagesy($src);
            $ch = (int) round($w * 9 / 16);
            $y = max(0, (int) round(($h - $ch) / 2));
            $dst = imagecreatetruecolor($w, min($ch, $h));
            imagecopy($dst, $src, 0, 0, 0, $y, $w, min($ch, $h));
            File::ensureDirectoryExists(public_path('vignettes'));
            $chemin = "vignettes/{$id}.webp";
            imagewebp($dst, public_path($chemin), 78);
            imagedestroy($src);
            imagedestroy($dst);

            return $chemin;
        } catch (Throwable) {
            return null;
        }
    }
}
