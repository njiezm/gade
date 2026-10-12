<?php

namespace App\Support;

class Duree
{
    /** 5400 -> « 1 h 30 », 1560 -> « 26 min » */
    public static function court(?int $secondes): string
    {
        if (! $secondes) {
            return '';
        }
        $min = (int) round($secondes / 60);
        if ($min < 60) {
            return $min.' min';
        }
        $h = intdiv($min, 60);
        $m = $min % 60;

        return $h.' h'.($m ? ' '.str_pad((string) $m, 2, '0', STR_PAD_LEFT) : '');
    }

    /** « 1:23:45 » ou « 45:10 » -> secondes ; accepte aussi « 90 » (minutes) et « 1h30 ». */
    public static function versSecondes(?string $saisie): ?int
    {
        $s = trim((string) $saisie);
        if ($s === '') {
            return null;
        }
        if (preg_match('/^(\d+)\s*h\s*(\d{1,2})?\s*(?:min)?$/i', $s, $m)) {
            return ((int) $m[1] * 60 + (int) ($m[2] ?? 0)) * 60;
        }
        if (preg_match('/^\d+$/', $s)) {
            return (int) $s * 60;
        }
        if (preg_match('/^(?:(\d+):)?(\d{1,2}):(\d{2})$/', $s, $m)) {
            return (int) $m[1] * 3600 + (int) $m[2] * 60 + (int) $m[3];
        }

        return null;
    }
}
