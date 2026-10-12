@extends('layouts.app')

@section('title', 'Gestion')

@section('content')
    <header class="entete-page">
        <span class="hand">Ka yo ka gadé ?</span>
        <h1>Gestion</h1>
        <p>L’activité de chaque profil : les derniers programmes regardés, sa liste et ce qu’il a déjà vu.</p>
    </header>

    <div class="page-wrap">
        <div class="d-flex flex-wrap gap-2 mb-4">
            <a class="btn btn-gade" href="{{ route('profils.gerer') }}">Gérer les profils</a>
            <a class="btn btn-velours" href="{{ route('compte') }}">Compte &amp; mot de passe</a>
            <a class="btn btn-velours" href="{{ route('coulisses.index') }}">Coulisses</a>
        </div>

        <div class="activite-grille">
            @foreach ($profils as $p)
                <section class="panneau activite" aria-labelledby="activite-{{ $p->id }}" style="--accent: {{ $p->couleur }}">
                    <div class="activite-entete">
                        @include('partials.avatar', ['profil' => $p, 'taille' => 'md'])
                        <div>
                            <h2 class="h4 mb-1" id="activite-{{ $p->id }}">{{ $p->prenom }}</h2>
                            <p class="small text-sable mb-0">
                                {{ $p->liste_count }} dans sa liste · {{ $p->vus_count }} déjà vu{{ $p->vus_count > 1 ? 's' : '' }}
                                @if ($p->gestionnaire) · gestionnaire @endif
                                @if ($p->enfant) · profil enfant @endif
                                @if ($p->aUnPin()) · PIN @endif
                            </p>
                        </div>
                    </div>
                    @if ($p->derniers->isEmpty())
                        <p class="small text-sable mb-0 mt-3">Rien regardé pour l’instant.</p>
                    @else
                        <ol class="activite-liste">
                            @foreach ($p->derniers as $pr)
                                @continue(! $pr->programme)
                                @php $ep = $pr->episode !== '' ? $pr->programme->episodes->firstWhere('youtube_id', $pr->episode) : null; @endphp
                                <li>
                                    <img src="{{ $ep?->vignetteUrl() ?? $pr->programme->vignetteUrl() }}" alt="" width="96" height="54" loading="lazy">
                                    <div>
                                        <span class="activite-titre">{{ $pr->programme->titre }}</span>
                                        <span class="activite-meta">
                                            @if ($ep){{ $ep->etiquette() }} · @endif
                                            {{ $pr->estTermine() ? 'terminé' : $pr->pourcent().' %' }} · {{ $pr->updated_at->locale('fr')->diffForHumans() }}
                                        </span>
                                    </div>
                                </li>
                            @endforeach
                        </ol>
                    @endif
                </section>
            @endforeach
        </div>
    </div>
@endsection
