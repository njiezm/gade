@extends('layouts.app')

@section('title', $titre)
@section('description', 'Parcourir la sélection GADÉ : films, séries, documentaires et programmes des Caraïbes, en français, filtrés par univers, genre et durée.')

@section('content')
    <header class="entete-page">
        <span class="hand">Ka ou ka chèché ?</span>
        <h1>{{ $titre }}</h1>
        @if ($filtres['genre'] === 'nouveaux-riches')
            <p class="mb-1"><strong>{{ config('gade.nouveaux_riches.sous_titre') }}.</strong> <span class="hand text-jaune" lang="gcf">{{ config('gade.nouveaux_riches.creole') }}</span></p>
            <p>{{ config('gade.nouveaux_riches.francais') }} Parcours d’entrepreneurs, histoires de fortunes et éducation financière, sans promesse d’argent facile.</p>
        @elseif ($filtres['genre'] && ($rg = config('gade.rangees_genres.'.$filtres['genre'])))
            <p class="mb-1"><strong>{{ $rg['sous_titre'] }}.</strong> <span class="hand text-jaune" lang="gcf">{{ $rg['creole'] }}</span></p>
            <p>{{ $rg['francais'] }}</p>
        @else
            <p>Filtrez par univers, genre ou durée, ou cherchez un titre, une île, un acteur.</p>
        @endif
    </header>

    <nav class="puces-univers" aria-label="Univers">
        <a href="{{ route('parcourir') }}" class="{{ ! $filtres['univers'] && ! $filtres['famille'] && ! $filtres['genre'] && $filtres['q'] === '' ? 'is-actif' : '' }}">Tout</a>
        @foreach (config('gade.univers') as $slug => $u)
            <a href="{{ route('parcourir', ['univers' => $slug]) }}" class="{{ $filtres['univers'] === $slug ? 'is-actif' : '' }}">{{ $u['label'] }}</a>
        @endforeach
        <a href="{{ route('parcourir', ['famille' => 1]) }}" class="{{ $filtres['famille'] && ! $filtres['univers'] ? 'is-actif' : '' }}">En famille</a>
    </nav>

    <form class="filtres" method="get" action="{{ route('parcourir') }}" data-filtres role="search">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-lg-4">
                <label class="form-label" for="f-q">Recherche</label>
                <input class="form-control" type="search" id="f-q" name="q" value="{{ $filtres['q'] }}" placeholder="Maigret, Martinique, volcans…">
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="f-univers">Univers</label>
                <select class="form-select" id="f-univers" name="univers">
                    <option value="">Tous</option>
                    @foreach (config('gade.univers') as $slug => $u)
                        <option value="{{ $slug }}" @selected($filtres['univers'] === $slug)>{{ $u['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="f-genre">Genre</label>
                <select class="form-select" id="f-genre" name="genre">
                    <option value="">Tous</option>
                    @foreach (config('gade.genres') as $slug => $g)
                        <option value="{{ $slug }}" @selected($filtres['genre'] === $slug)>{{ $g }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="f-duree">Durée</label>
                <select class="form-select" id="f-duree" name="duree">
                    <option value="">Toutes</option>
                    @foreach (config('gade.durees') as $slug => $d)
                        <option value="{{ $slug }}" @selected($filtres['duree'] === $slug)>{{ $d['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label" for="f-tri">Trier</label>
                <select class="form-select" id="f-tri" name="tri">
                    <option value="selection" @selected($filtres['tri'] === 'selection')>Notre sélection</option>
                    <option value="recents" @selected($filtres['tri'] === 'recents')>Ajouts récents</option>
                    <option value="titre" @selected($filtres['tri'] === 'titre')>Titre (A → Z)</option>
                    <option value="duree" @selected($filtres['tri'] === 'duree')>Plus courts d’abord</option>
                </select>
            </div>
            <div class="col-12 d-flex flex-wrap align-items-center gap-3">
                <div class="form-check form-switch m-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="f-famille" name="famille" value="1" @checked($filtres['famille'])>
                    <label class="form-check-label" for="f-famille">Tout public seulement (en famille)</label>
                </div>
                <button class="btn btn-gade ms-auto" type="submit">Filtrer</button>
                <a class="btn-lien" href="{{ route('parcourir') }}">Effacer</a>
            </div>
        </div>
    </form>

    <section class="resultats" aria-labelledby="titre-resultats">
        <h2 class="visually-hidden" id="titre-resultats">Résultats</h2>
        <p class="resultats-compte" role="status">{{ $programmes->count() }} programme{{ $programmes->count() > 1 ? 's' : '' }}</p>
        @if ($programmes->isEmpty())
            <div class="vide">
                <span class="hand">Ayen… rien trouvé.</span>
                Essayez un autre mot ou retirez un filtre.
            </div>
        @else
            <ul class="grille">
                @foreach ($programmes as $p)
                    <li>@include('partials.carte', ['p' => $p, 'lazy' => $loop->index > 7])</li>
                @endforeach
            </ul>
        @endif
    </section>
@endsection
