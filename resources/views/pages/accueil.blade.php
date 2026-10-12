@extends('layouts.app')

@section('content')
    {{-- À la une --}}
    <section class="une" data-une aria-label="À la une">
        @foreach ($une as $p)
            @php $genres = $p->genresLabels(); @endphp
            <article class="une-slide {{ $loop->first ? 'is-active' : '' }}" data-une-slide aria-labelledby="une-{{ $p->slug }}">
                <div class="une-image">
                    <img src="{{ $p->afficheUrl() }}" alt="" width="1280" height="720" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                </div>
                <div class="une-contenu">
                    <span class="une-etiquette">À la une, ce soir lakay</span>
                    <h1 class="une-titre" id="une-{{ $p->slug }}">{{ $p->titre }}</h1>
                    <div class="une-meta">
                        <span class="pastille">{{ $p->universLabel() }}</span>
                        <span class="pastille">{{ $p->dureeLabel() }}</span>
                        @if ($p->annee)<span class="pastille">{{ $p->annee }}</span>@endif
                        @foreach (array_slice($genres, 0, 2) as $g)<span class="pastille">{{ $g }}</span>@endforeach
                        @if ($p->tout_public)<span class="pastille famille">Tout public</span>@endif
                    </div>
                    <p class="une-desc">{{ $p->description }}</p>
                    <div class="une-actions">
                        <a class="btn btn-gade btn-lg" href="{{ $p->urlLecture() }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15a1 1 0 0 0 1.5.86l12.5-7.5a1 1 0 0 0 0-1.72L8.5 3.64A1 1 0 0 0 7 4.5z" fill="currentColor"/></svg>
                            Gadé
                        </a>
                        <a class="btn btn-velours btn-lg" href="{{ $p->urlFiche() }}">Plus d’infos<span class="visually-hidden"> sur {{ $p->titre }}</span></a>
                        @include('partials.bouton-liste', ['programme' => $p, 'grand' => true])
                    </div>
                </div>
            </article>
        @endforeach

        @if ($une->count() > 1)
            <div class="une-choix" role="group" aria-label="Choisir le programme à la une">
                @foreach ($une as $p)
                    <button type="button" data-une-choix aria-pressed="{{ $loop->first ? 'true' : 'false' }}">
                        <img src="{{ $p->vignetteUrl() }}" alt="" width="46" height="26" loading="lazy">
                        <span>{{ $p->titre }}</span>
                    </button>
                @endforeach
            </div>
        @endif
    </section>

    <div class="bienvenue">
        <span class="hand">Ka ou lé gadé jodi-a, {{ $profilActif->prenom }} ?</span>
        <p>{{ $nbProgrammes }} programmes choisis avec soin, en français, publiés par leurs ayants droit.@if ($profilActif->enfant) Profil enfant : uniquement des programmes tout public.@endif</p>
    </div>

    <div class="mt-4">
        {{-- Données du profil actif (base de données) --}}
        @if ($kontinye->isNotEmpty())
            @include('partials.rangee', ['id' => 'kontinye', 'titre' => 'Kontinyé gadé', 'sous_titre' => 'Reprenez là où vous vous étiez arrêté·e', 'lien' => null, 'items' => $kontinye, 'reprises' => true])
        @endif
        @if ($maListe->isNotEmpty())
            @include('partials.rangee', ['id' => 'ma-liste', 'titre' => 'Ma liste', 'sous_titre' => 'Gardé pour plus tard', 'lien' => route('ma-liste'), 'items' => $maListe])
        @endif

        @foreach ($rangees as $r)
            @include('partials.rangee', $r + ['eager' => $loop->first])
        @endforeach

        @if ($dejaVu->isNotEmpty())
            @include('partials.rangee', ['id' => 'deja-vu', 'titre' => 'Déjà vu', 'sous_titre' => 'Pour revoir un bon moment', 'lien' => route('ma-liste').'#deja-vu', 'items' => $dejaVu])
        @endif
    </div>
@endsection
