<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Position de lecture d'un profil sur un programme (ou un épisode de série). */
class Progression extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['termine_le' => 'datetime', 'position' => 'integer', 'duree' => 'integer'];
    }

    public function profil(): BelongsTo
    {
        return $this->belongsTo(Profil::class);
    }

    public function programme(): BelongsTo
    {
        return $this->belongsTo(Programme::class);
    }

    public function estTermine(): bool
    {
        return $this->duree > 0 && $this->position >= $this->duree - 60;
    }

    public function estEnCours(): bool
    {
        return $this->position > 30 && ! $this->estTermine();
    }

    public function pourcent(): int
    {
        return $this->duree ? (int) min(100, max(3, round($this->position / $this->duree * 100))) : 0;
    }

    /** Enregistre une position (envoyée par le lecteur ou importée). */
    public static function enregistrer(Profil $profil, Programme $programme, ?string $episode, int $t, int $d, $quand = null): self
    {
        $prog = static::firstOrNew(['profil_id' => $profil->id, 'programme_id' => $programme->id, 'episode' => $episode ?: '']);
        $prog->position = max(0, min($t, $d));
        $prog->duree = $d;
        if ($prog->estTermine()) {
            $prog->termine_le = $quand ?? now();
        }
        if ($quand) {
            $prog->created_at ??= $quand;
            $prog->updated_at = $quand;
            $prog->timestamps = false;
        }
        $prog->save();
        $prog->timestamps = true;

        return $prog;
    }
}
