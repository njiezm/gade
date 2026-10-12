<?php

namespace App\Support;

/** Galerie d'avatars des profils (SVG dans public/images/avatars, liste générée par resources/avatars/avatars.mjs). */
class Avatars
{
    private static ?array $manifeste = null;

    /** @return array<int,array{cle:string,label:string,texte:string,avatars:array<int,array{cle:string,nom:string}>}> */
    public static function themes(): array
    {
        if (self::$manifeste === null) {
            $f = public_path('images/avatars/avatars.json');
            self::$manifeste = is_file($f) ? (json_decode(file_get_contents($f), true)['themes'] ?? []) : [];
        }

        return self::$manifeste;
    }

    /** @return array<string,string> clé => nom */
    public static function tous(): array
    {
        $out = [];
        foreach (self::themes() as $t) {
            foreach ($t['avatars'] as $a) {
                $out[$a['cle']] = $a['nom'];
            }
        }

        return $out;
    }

    public static function existe(?string $cle): bool
    {
        return $cle !== null && array_key_exists($cle, self::tous());
    }

    public static function nom(string $cle): string
    {
        return self::tous()[$cle] ?? $cle;
    }

    public static function url(?string $cle): string
    {
        return asset('images/avatars/'.(self::existe($cle) ? $cle : 'clap').'.svg');
    }
}
