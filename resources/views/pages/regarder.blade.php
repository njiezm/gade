@extends('layouts.app')

@section('title', ($ep ? $ep->titre.' — ' : '').$p->titre)
@section('description', \Illuminate\Support\Str::limit($p->description, 155))
@section('og_image', $p->afficheUrl())

@section('content')
    @php $poster = $ep?->vignetteUrl() ?? $p->afficheUrl(); @endphp
    <div class="salle" data-lecteur
         data-video="{{ $videoId }}"
         data-slug="{{ $p->slug }}"
         data-episode="{{ $ep?->youtube_id }}"
         @if ($memo) data-reprise="{{ $memo->position }}" data-reprise-duree="{{ $memo->duree }}" @endif
         @if ($suivant) data-suivant="{{ $p->urlLecture($suivant) }}" data-suivant-titre="{{ $suivant->etiquette() }} · {{ $suivant->titre }}" @endif>

        <nav class="fil-ariane" aria-label="Fil d’Ariane">
            <a href="{{ route('accueil') }}">Accueil</a><span aria-hidden="true">›</span>
            <a href="{{ $p->urlFiche() }}">{{ $p->titre }}</a>
            @if ($ep)<span aria-hidden="true">›</span><span>{{ $ep->etiquette() }}</span>@endif
        </nav>

        <div class="salle-grille">
            <div>
                <div class="ecran-cadre">
                    <div class="ecran" data-ecran>
                        <div class="ecran-lecteur" data-ecran-lecteur></div>

                        <div class="ecran-attente" data-attente>
                            <img src="{{ $poster }}" alt="" width="1280" height="720">
                            <button class="btn btn-gade btn-lg" type="button" data-lancer>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15a1 1 0 0 0 1.5.86l12.5-7.5a1 1 0 0 0 0-1.72L8.5 3.64A1 1 0 0 0 7 4.5z" fill="currentColor"/></svg>
                                Lancer la lecture
                            </button>
                        </div>

                        <div class="ecran-voile" data-voile-reprise hidden role="dialog" aria-label="Reprendre la lecture">
                            <div>
                                <span class="hand">Kontinyé gadé ?</span>
                                <p>Vous vous étiez arrêtés à <strong data-reprise-temps>0:00</strong>.</p>
                                <div class="boutons">
                                    <button class="btn btn-gade" type="button" data-reprendre-ici>Reprendre</button>
                                    <button class="btn btn-velours" type="button" data-depuis-debut>Depuis le début</button>
                                </div>
                            </div>
                        </div>

                        <div class="ecran-voile" data-voile-suivant hidden role="dialog" aria-label="Épisode suivant">
                            <div>
                                <span class="compte-a-rebours" data-compte aria-live="polite">10</span>
                                <span class="hand">Épisode suivant…</span>
                                <p data-suivant-titre></p>
                                <div class="boutons">
                                    <button class="btn btn-gade" type="button" data-maintenant>Gadé tout de suite</button>
                                    <button class="btn btn-velours" type="button" data-annuler>Rester ici</button>
                                </div>
                            </div>
                        </div>

                        <div class="ecran-voile" data-voile-erreur hidden role="alert">
                            <div>
                                <span class="hand">Woy… la vidéo ne se lance pas.</span>
                                <p>Elle a peut-être été retirée par son ayant droit. Vous pouvez la chercher directement sur YouTube.</p>
                                <div class="boutons">
                                    <a class="btn btn-velours" href="https://www.youtube.com/watch?v={{ $videoId }}" target="_blank" rel="noopener">Ouvrir sur YouTube ↗</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="salle-infos">
                    <h1 class="salle-titre">{{ $p->titre }}</h1>
                    @if ($ep)<p class="salle-sous-titre">{{ $ep->etiquette() }} · {{ $ep->titre }}</p>@endif
                    <div class="une-meta">
                        <span class="pastille">{{ $ep ? $ep->dureeLabel() : $p->dureeLabel() }}</span>
                        @foreach (array_slice($p->genresLabels(), 0, 2) as $g)<span class="pastille">{{ $g }}</span>@endforeach
                        @if ($p->tout_public)<span class="pastille famille">Tout public</span>@endif
                    </div>
                    <div class="salle-actions">
                        <button class="btn btn-velours" type="button" data-plein-ecran>
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                            Plein écran
                        </button>
                        @if ($suivant)
                            <a class="btn btn-velours" href="{{ $p->urlLecture($suivant) }}">Épisode suivant →</a>
                        @endif
                        @include('partials.bouton-liste', ['programme' => $p])
                        @include('partials.sur-la-tele', ['video' => $videoId, 'slug' => $p->slug, 'episode' => $ep?->youtube_id, 'contexte' => 'lecteur', 'lecture' => url()->current()])
                        <a class="btn btn-velours" href="{{ $p->urlFiche() }}">Fiche du programme</a>
                    </div>
                    <p class="salle-note mb-1">Vidéo diffusée par <a href="{{ $p->chaineUrl() }}" target="_blank" rel="noopener">{{ $p->chaine_nom }}</a> via le lecteur intégré de YouTube (youtube-nocookie). Votre position de lecture est enregistrée dans le profil de {{ $profilActif->prenom }}.</p>
                </div>
            </div>

            @if ($p->estSerie())
                <aside class="salle-cote" aria-labelledby="titre-liste-episodes">
                    <h2 id="titre-liste-episodes">Épisodes</h2>
                    @foreach ($saisons as $num => $eps)
                        @if ($saisons->count() > 1)<p class="text-sable fw-bold small mb-2 mt-3">Saison {{ $num }}</p>@endif
                        <ol class="episodes">
                            @foreach ($eps as $e)
                                <li>
                                    <a class="episode {{ $ep && $ep->id === $e->id ? 'is-courant' : '' }}" href="{{ $p->urlLecture($e) }}" @if ($ep && $ep->id === $e->id) aria-current="true" @endif>
                                        <span class="episode-image"><img src="{{ $e->vignetteUrl() }}" alt="" width="480" height="270" loading="lazy"></span>
                                        <span>
                                            <span class="episode-num">{{ $e->etiquette() }}</span>
                                            <span class="episode-titre">{{ $e->titre }}</span>
                                            <span class="episode-duree">{{ $e->dureeLabel() }}</span>
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ol>
                    @endforeach
                </aside>
            @else
                <aside class="salle-cote">
                    <h2>À propos</h2>
                    <p class="mb-2">{{ $p->description }}</p>
                    @if ($p->annee)<p class="text-sable small mb-0">Année : {{ $p->annee }}</p>@endif
                </aside>
            @endif
        </div>
    </div>

    @if ($similaires->isNotEmpty())
        @include('partials.rangee', ['id' => 'apres', 'titre' => 'Et après ?', 'sous_titre' => 'Dans le même esprit', 'lien' => null, 'items' => $similaires])
    @endif
@endsection
