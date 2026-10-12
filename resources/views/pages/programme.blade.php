@extends('layouts.app')

@section('title', $p->titre)
@section('description', \Illuminate\Support\Str::limit($p->description, 155))
@section('og_image', $p->afficheUrl())

@push('head')
    <script type="application/ld+json">
        {!! json_encode(array_filter([
            '@'.'context' => 'https://schema.org',
            '@type' => $p->estSerie() ? 'TVSeries' : ($p->univers === 'films' ? 'Movie' : 'VideoObject'),
            'name' => $p->titre,
            'description' => $p->description,
            'inLanguage' => 'fr',
            'image' => $p->afficheUrl(),
            'thumbnailUrl' => $p->vignetteUrl(),
            'uploadDate' => $p->estSerie() ? null : $p->publie_le?->toDateString(),
            'duration' => $p->estSerie() || ! $p->duree_secondes ? null : 'PT'.intdiv($p->duree_secondes, 60).'M'.($p->duree_secondes % 60).'S',
            'embedUrl' => $p->youtube_id ? 'https://www.youtube-nocookie.com/embed/'.$p->youtube_id : null,
            'numberOfEpisodes' => $p->estSerie() ? $p->episodesVisibles()->count() : null,
            'copyrightHolder' => $p->ayant_droit ? ['@type' => 'Organization', 'name' => $p->ayant_droit] : null,
        ]), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
    </script>
@endpush

@section('content')
    @php $genres = $p->genresLabels(); @endphp
    <article data-fiche="{{ $p->slug }}">
        <header class="fiche-tete">
            <div class="fiche-fond">
                <img src="{{ $p->afficheUrl() }}" alt="" width="1280" height="720" fetchpriority="high">
            </div>
            <div class="fiche-contenu">
                <nav class="fil-ariane" aria-label="Fil d’Ariane">
                    <a href="{{ route('accueil') }}">Accueil</a><span aria-hidden="true">›</span>
                    <a href="{{ route('parcourir', ['univers' => $p->univers]) }}">{{ $p->universLabel() }}</a>
                </nav>
                <h1 class="fiche-titre">{{ $p->titre }}</h1>
                <div class="une-meta">
                    <span class="pastille">{{ $p->dureeLabel() }}</span>
                    @if ($p->annee)<span class="pastille">{{ $p->annee }}</span>@endif
                    @foreach ($genres as $slug => $g)
                        <a class="pastille" href="{{ route('parcourir', ['genre' => $slug]) }}">{{ $g }}</a>
                    @endforeach
                    @if ($p->tout_public)<a class="pastille famille" href="{{ route('parcourir', ['famille' => 1]) }}">Tout public</a>@endif
                    @if ($p->disponible_jusqu_au && $p->disponible_jusqu_au->isFuture() && $p->disponible_jusqu_au->diffInDays(now()) < 120)
                        <span class="pastille alerte" title="Date annoncée par l’ayant droit">Jusqu’au {{ $p->disponible_jusqu_au->translatedFormat('j F Y') }}</span>
                    @endif
                </div>
                <div class="une-actions">
                    <a class="btn btn-gade btn-lg" href="{{ $reprise['bouton']['href'] ?? $p->urlLecture() }}" data-reprendre>
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 4.5v15a1 1 0 0 0 1.5.86l12.5-7.5a1 1 0 0 0 0-1.72L8.5 3.64A1 1 0 0 0 7 4.5z" fill="currentColor"/></svg>
                        <span data-reprendre-label>{{ $reprise['bouton']['label'] ?? ($p->estSerie() ? 'Lecture · '.($p->premierEpisode()?->etiquette() ?? '') : 'Lecture') }}</span>
                    </a>
                    @include('partials.bouton-liste', ['programme' => $p, 'grand' => true])
                    @include('partials.sur-la-tele', ['video' => $p->youtube_id ?? $p->premierEpisode()?->youtube_id, 'slug' => $p->slug, 'episode' => null, 'contexte' => 'fiche', 'lecture' => $p->urlLecture()])
                </div>
            </div>
        </header>

        <div class="page-wrap">
            <div class="row g-4 mb-4">
                <div class="col-lg-8">
                    <p class="fiche-desc">{{ $p->description }}</p>
                    <div class="fiche-source">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M23 7.2a3 3 0 0 0-2.1-2.1C19 4.6 12 4.6 12 4.6s-7 0-8.9.5A3 3 0 0 0 1 7.2 31 31 0 0 0 .5 12a31 31 0 0 0 .5 4.8 3 3 0 0 0 2.1 2.1c1.9.5 8.9.5 8.9.5s7 0 8.9-.5a3 3 0 0 0 2.1-2.1 31 31 0 0 0 .5-4.8 31 31 0 0 0-.5-4.8z" fill="currentColor"/><path d="M9.8 15.1l5.8-3.1-5.8-3.1z" fill="#fff"/></svg>
                        <span>Publié sur YouTube par <strong>{{ $p->chaine_nom }}</strong>@if ($p->ayant_droit && $p->ayant_droit !== $p->chaine_nom) · ayant droit : {{ $p->ayant_droit }}@endif</span>
                        @if ($p->chaineUrl())
                            <a href="{{ $p->chaineUrl() }}" target="_blank" rel="noopener">{{ str_starts_with((string) $p->ayant_droit, 'Source non officielle') ? 'Voir la chaîne' : 'Voir la chaîne officielle' }} ↗</a>
                        @endif
                    </div>
                </div>
                <div class="col-lg-4">
                    <dl class="row small text-sable mb-0">
                        <dt class="col-5 fw-semibold">Univers</dt><dd class="col-7">{{ $p->universLabel() }}</dd>
                        <dt class="col-5 fw-semibold">Durée</dt><dd class="col-7">{{ $p->dureeLabel() }}</dd>
                        @if ($p->annee)<dt class="col-5 fw-semibold">Année</dt><dd class="col-7">{{ $p->annee }}</dd>@endif
                        <dt class="col-5 fw-semibold">Public</dt><dd class="col-7">{{ $p->tout_public ? 'Tout public, en famille' : 'Adulte' }}</dd>
                        <dt class="col-5 fw-semibold">Langue</dt><dd class="col-7">Français</dd>
                        @if ($p->verifie_le)<dt class="col-5 fw-semibold">Vérifié le</dt><dd class="col-7">{{ $p->verifie_le->format('d/m/Y') }}</dd>@endif
                    </dl>
                </div>
            </div>

            @if ($p->estSerie() && $saisons->isNotEmpty())
                <section class="mb-5" aria-labelledby="titre-episodes" data-saisons>
                    <h2 class="section-titre" id="titre-episodes">Épisodes</h2>
                    @php($groupes = $p->groupesEpisodes())
                    @if ($groupes->count() > 1)
                        <div class="saisons-onglets" role="tablist" aria-label="{{ $saisons->count() > 1 ? 'Saisons' : 'Épisodes par tranche' }}">
                            @foreach ($groupes as $g)
                                <button type="button" role="tab" id="onglet-{{ $g['cle'] }}" aria-controls="saison-{{ $g['cle'] }}" aria-selected="{{ $loop->first ? 'true' : 'false' }}" tabindex="{{ $loop->first ? 0 : -1 }}">{{ $g['label'] }}</button>
                            @endforeach
                        </div>
                    @endif
                    @foreach ($groupes as $g)
                        <div id="saison-{{ $g['cle'] }}" @if ($groupes->count() > 1) role="tabpanel" aria-labelledby="onglet-{{ $g['cle'] }}" @endif @if (! $loop->first) hidden @endif>
                            <ol class="episodes">
                                @foreach ($g['eps'] as $e)
                                    <li>
                                        <a class="episode" href="{{ $p->urlLecture($e) }}" data-episode="{{ $e->youtube_id }}">
                                            <span class="episode-image"><img src="{{ $e->vignetteUrl() }}" alt="" width="480" height="270" loading="lazy">@isset($reprise['episodes'][$e->youtube_id])<span class="carte-progression" aria-hidden="true"><span style="width: {{ $reprise['episodes'][$e->youtube_id] }}%"></span></span>@endisset</span>
                                            <span>
                                                <span class="episode-num">{{ $saisons->count() > 1 ? $e->etiquette() : 'Épisode '.$e->numero }}</span>
                                                <span class="episode-titre">{{ $e->titre }}</span>
                                                @if ($e->description)<span class="episode-desc">{{ $e->description }}</span>@endif
                                                <span class="episode-duree">{{ $e->dureeLabel() }}</span>
                                            </span>
                                        </a>
                                    </li>
                                @endforeach
                            </ol>
                        </div>
                    @endforeach
                </section>
            @endif
        </div>

        @if ($similaires->isNotEmpty())
            @include('partials.rangee', ['id' => 'similaires', 'titre' => 'Dans le même esprit', 'sous_titre' => 'D’autres programmes à gadé ensuite', 'lien' => route('parcourir', ['univers' => $p->univers]), 'items' => $similaires])
        @endif
    </article>
@endsection
