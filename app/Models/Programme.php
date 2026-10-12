<?php

namespace App\Models;

use App\Support\Duree;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class Programme extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'genres' => 'array',
            'tout_public' => 'boolean',
            'a_la_une' => 'boolean',
            'masque' => 'boolean',
            'disponible_jusqu_au' => 'date',
            'verifie_le' => 'datetime',
            'publie_le' => 'datetime',
        ];
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class)->orderBy('saison')->orderBy('numero');
    }

    /** Programmes affichés au public : non masqués et toujours disponibles. */
    public function scopeVisible(Builder $q): Builder
    {
        return $q->where('masque', false)->where('statut_verif', '!=', 'indisponible');
    }

    /** Catalogue autorisé pour un profil : un profil enfant ne voit que les programmes « tout public ». */
    public function scopePourProfil(Builder $q, ?Profil $profil = null): Builder
    {
        $profil ??= Profil::actif();

        return $profil?->enfant ? $q->where('tout_public', true) : $q;
    }

    /** Le profil (actif par défaut) peut-il voir ce programme ? */
    public function accessiblePour(?Profil $profil = null): bool
    {
        $profil ??= Profil::actif();

        return ! $this->masque && $this->statut_verif !== 'indisponible' && (! $profil?->enfant || $this->tout_public);
    }

    public function scopeTri(Builder $q): Builder
    {
        return $q->orderByDesc('a_la_une')->orderByDesc('ordre')->orderByDesc('created_at')->orderBy('titre');
    }

    public function scopeGenre(Builder $q, string $genre): Builder
    {
        return $q->where('genres', 'like', '%"'.$genre.'"%');
    }

    /** Durée en minutes (pour une série : durée typique d'un épisode). */
    public function scopeDureeEntre(Builder $q, ?int $min, ?int $max): Builder
    {
        if ($min) {
            $q->where('duree_secondes', '>=', $min * 60);
        }
        if ($max) {
            $q->where('duree_secondes', '<', $max * 60);
        }

        return $q;
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function estSerie(): bool
    {
        return $this->format === 'serie';
    }

    /** Épisodes visibles, groupés par saison. */
    public function saisons(): Collection
    {
        return $this->episodesVisibles()->groupBy('saison');
    }

    /**
     * Onglets de la fiche : une saison par onglet ; une très longue saison unique (sitcoms, feuilletons)
     * est découpée en tranches de 50 épisodes (« Épisodes 1–50 »…), sans toucher à la numérotation.
     *
     * @return Collection<int,array{cle:string,label:string,eps:Collection}>
     */
    public function groupesEpisodes(int $tranche = 50, int $seuil = 60): Collection
    {
        $saisons = $this->saisons();
        if ($saisons->count() === 1 && $saisons->first()->count() > $seuil) {
            return $saisons->first()->values()->chunk($tranche)->values()->map(fn (Collection $eps, int $i) => [
                'cle' => 't'.($i + 1), 'label' => 'Épisodes '.$eps->first()->numero.'–'.$eps->last()->numero, 'eps' => $eps->values(),
            ]);
        }

        return $saisons->map(fn (Collection $eps, $num) => ['cle' => 's'.$num, 'label' => 'Saison '.$num, 'eps' => $eps->values()])->values();
    }

    public function episodesVisibles(): Collection
    {
        $eps = $this->relationLoaded('episodes') ? $this->episodes : $this->episodes()->get();

        return $eps->filter(fn (Episode $e) => ! $e->masque && $e->statut_verif !== 'indisponible')->values();
    }

    public function premierEpisode(): ?Episode
    {
        return $this->episodesVisibles()->first();
    }

    public function urlFiche(): string
    {
        return route('programme', $this);
    }

    public function urlLecture(?Episode $episode = null): string
    {
        $episode ??= $this->estSerie() ? $this->premierEpisode() : null;

        return $episode ? route('regarder.episode', [$this, $episode->youtube_id]) : route('regarder', $this);
    }

    public function vignetteUrl(): string
    {
        if ($this->vignette) {
            return asset($this->vignette);
        }
        $id = $this->youtube_id ?? $this->premierEpisode()?->youtube_id;

        return $id ? "https://i.ytimg.com/vi/{$id}/hqdefault.jpg" : asset('images/brand/og-gade.jpg');
    }

    public function afficheUrl(): string
    {
        return $this->affiche ? asset($this->affiche) : $this->vignetteUrl();
    }

    public function universLabel(): string
    {
        return config("gade.univers.{$this->univers}.label", ucfirst($this->univers));
    }

    /** @return array<string,string> slug => libellé */
    public function genresLabels(): array
    {
        $labels = config('gade.genres');

        return collect($this->genres ?? [])->mapWithKeys(fn ($g) => [$g => $labels[$g] ?? $g])->all();
    }

    public function dureeLabel(): string
    {
        if ($this->estSerie()) {
            $n = $this->episodesVisibles()->count();
            $txt = $n.' '.($n > 1 ? 'épisodes' : 'épisode');

            return $this->duree_secondes ? $txt.' · ~'.Duree::court($this->duree_secondes) : $txt;
        }

        return Duree::court($this->duree_secondes);
    }

    public function chaineUrl(): ?string
    {
        if ($this->chaine_url) {
            return $this->chaine_url;
        }

        return $this->chaine_id ? 'https://www.youtube.com/channel/'.$this->chaine_id : null;
    }

    /** Programmes proches : même univers ou genres en commun. */
    public function similaires(int $n = 12): Collection
    {
        $genres = $this->genres ?? [];

        return static::visible()->pourProfil()->where('id', '!=', $this->id)->get()
            ->map(function (Programme $p) use ($genres) {
                $score = count(array_intersect($genres, $p->genres ?? [])) * 2 + ($p->univers === $this->univers ? 1 : 0)
                    + ($p->chaine_id && $p->chaine_id === $this->chaine_id ? 1 : 0);

                return [$score, $p];
            })
            ->filter(fn ($x) => $x[0] > 0)
            ->sortByDesc(fn ($x) => $x[0] * 1000 + ($x[1]->ordre ?? 0))
            ->take($n)
            ->map(fn ($x) => $x[1])
            ->values();
    }
}
