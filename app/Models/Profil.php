<?php

namespace App\Models;

use App\Support\Avatars;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

/**
 * Profil « façon Netflix » du compte de la famille.
 * Le profil gestionnaire (N'jie) est le seul à pouvoir gérer les autres profils et entrer dans les coulisses.
 */
class Profil extends Model
{
    protected $table = 'profils';

    protected $fillable = ['prenom', 'avatar', 'couleur', 'enfant', 'ordre'];

    protected $hidden = ['pin'];

    protected function casts(): array
    {
        return [
            'gestionnaire' => 'boolean',
            'enfant' => 'boolean',
            'pin_bloque_jusqua' => 'datetime',
        ];
    }

    /** Profil actif de la requête (choisi sur l'écran « Ki moun ka gadé ? »), ou null. */
    public static function actif(): ?self
    {
        return app()->bound('profil.actif') ? app('profil.actif') : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function progressions(): HasMany
    {
        return $this->hasMany(Progression::class);
    }

    public function liste(): BelongsToMany
    {
        return $this->belongsToMany(Programme::class, 'profil_liste')->withTimestamps();
    }

    public function scopeOrdonnes(Builder $q): Builder
    {
        return $q->orderByDesc('gestionnaire')->orderBy('ordre')->orderBy('id');
    }

    public function avatarUrl(): string
    {
        return Avatars::url($this->avatar);
    }

    public function initiale(): string
    {
        return mb_strtoupper(mb_substr($this->prenom, 0, 1));
    }

    // ---------------- PIN (4 chiffres, haché, 5 essais) ----------------

    public function aUnPin(): bool
    {
        return ! empty($this->pin);
    }

    public function definirPin(?string $pin): void
    {
        $this->pin = $pin ? Hash::make($pin) : null;
        $this->pin_echecs = 0;
        $this->pin_bloque_jusqua = null;
        $this->save();
    }

    public function pinBloque(): bool
    {
        return $this->pin_bloque_jusqua !== null && $this->pin_bloque_jusqua->isFuture();
    }

    /** Vérifie un PIN : 'ok', 'faux' ou 'bloque' (après 5 erreurs, blocage temporaire). */
    public function verifierPin(string $pin): string
    {
        if (! $this->aUnPin()) {
            return 'ok';
        }
        if ($this->pinBloque()) {
            return 'bloque';
        }
        if (preg_match('/^\d{4}$/', $pin) && Hash::check($pin, $this->pin)) {
            $this->forceFill(['pin_echecs' => 0, 'pin_bloque_jusqua' => null])->save();

            return 'ok';
        }

        $echecs = $this->pin_echecs + 1;
        if ($echecs >= (int) config('gade.profils.pin_essais', 5)) {
            $this->forceFill(['pin_echecs' => 0, 'pin_bloque_jusqua' => now()->addMinutes((int) config('gade.profils.pin_blocage_minutes', 15))])->save();

            return 'bloque';
        }
        $this->forceFill(['pin_echecs' => $echecs])->save();

        return 'faux';
    }

    public function essaisRestants(): int
    {
        return max(0, (int) config('gade.profils.pin_essais', 5) - $this->pin_echecs);
    }

    // ---------------- Données de visionnage ----------------

    private ?array $idsListe = null;

    public function aDansSaListe(Programme $p): bool
    {
        $this->idsListe ??= $this->liste()->pluck('programmes.id')->all();

        return in_array($p->id, $this->idsListe, true);
    }
}
