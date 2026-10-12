@extends('layouts.app')

@section('title', 'Ma liste')
@section('description', 'Les programmes gardés pour plus tard sur GADÉ.')

@section('content')
    <header class="entete-page">
        <span class="hand">Pou pita…</span>
        <h1>Ma liste</h1>
        <p>
            @if ($programmes->isNotEmpty())
                {{ $programmes->count() }} programme{{ $programmes->count() > 1 ? 's' : '' }} gardé{{ $programmes->count() > 1 ? 's' : '' }} pour plus tard par {{ $profilActif->prenom }}.
            @else
                Les programmes que {{ $profilActif->prenom }} garde pour plus tard.
            @endif
        </p>
    </header>
    <section class="resultats" aria-label="Programmes de ma liste">
        @if ($programmes->isEmpty())
            <div class="vide">
                <span class="hand">Ou poko mété ayen !</span>
                Touchez « Ma liste » sur une fiche pour garder un film, une série ou un documentaire pour plus tard.
                <div class="mt-3"><a class="btn btn-gade" href="{{ route('accueil') }}">Explorer la sélection</a></div>
            </div>
        @else
            <ul class="grille">
                @foreach ($programmes as $p)
                    <li>@include('partials.carte', ['p' => $p])</li>
                @endforeach
            </ul>
        @endif
    </section>

    @if ($dejaVu->isNotEmpty())
        <section class="resultats" aria-labelledby="deja-vu">
            <h2 class="section-titre" id="deja-vu">Déjà vu</h2>
            <ul class="grille">
                @foreach ($dejaVu as $p)
                    <li>@include('partials.carte', ['p' => $p])</li>
                @endforeach
            </ul>
        </section>
    @endif
@endsection
