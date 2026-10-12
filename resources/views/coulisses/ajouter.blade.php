@extends('layouts.app')

@section('title', 'Ajouter un programme — Coulisses')
@section('noindex', true)

@section('content')
    @php
        $titre = old('titre', $proposition?->titre ?? ($o['titre'] ?? ''));
        $chaine = $o['chaine_nom'] ?? $proposition?->chaine_nom;
        $chaineUrl = $o['chaine_url'] ?? $proposition?->chaine_url;
        $genresChoisis = old('genres', $proposition?->genres ?? []);
        $univers = old('univers', $proposition?->univers);
    @endphp
    <div class="coulisses">
        <div class="coulisses-entete">
            <div>
                <a class="small" href="{{ route('coulisses.index') }}">← Retour aux coulisses</a>
                <h1>{{ $proposition ? 'Valider une proposition' : 'Ajouter un programme' }}</h1>
            </div>
        </div>

        @if ($errors->any())
            <div class="flash erreur" role="alert" data-flash>{{ $errors->first() }}</div>
        @endif

        <div class="row g-4">
            <div class="col-lg-5">
                <div class="apercu-video mb-3">
                    <img src="https://i.ytimg.com/vi/{{ $id }}/hqdefault.jpg" alt="Vignette de la vidéo" width="480" height="360">
                </div>
                <div class="panneau">
                    <p class="mb-1"><strong>Chaîne :</strong> {{ $chaine ?: 'inconnue' }}</p>
                    @if ($confiance)
                        <p class="pastille famille mb-2">✓ Chaîne de la liste de confiance</p>
                    @else
                        <p class="pastille alerte mb-2">⚠ Chaîne hors liste de confiance</p>
                        <p class="small text-sable">Vérifiez qu’il s’agit bien de l’ayant droit (diffuseur, producteur, distributeur officiel). En cas de doute, n’ajoutez pas la vidéo.</p>
                    @endif
                    @if (($o['statut'] ?? null) === 'erreur')
                        <p class="small text-sable mb-0">YouTube n’a pas répondu : les informations sont à saisir à la main.</p>
                    @endif
                    @if ($proposition?->motif)<p class="small text-sable mt-2 mb-0"><strong>Motif :</strong> {{ $proposition->motif }}</p>@endif
                    <a class="small d-inline-block mt-2" href="https://www.youtube.com/watch?v={{ $id }}" target="_blank" rel="noopener">Regarder sur YouTube pour vérifier (langue, durée, version complète) ↗</a>
                </div>
            </div>
            <div class="col-lg-7">
                <form class="panneau" method="post" action="{{ route('coulisses.enregistrer') }}">
                    @csrf
                    <input type="hidden" name="youtube_id" value="{{ $id }}">
                    <input type="hidden" name="chaine_nom" value="{{ $chaine }}">
                    <input type="hidden" name="chaine_url" value="{{ $chaineUrl }}">
                    @if ($proposition)<input type="hidden" name="proposition_id" value="{{ $proposition->id }}">@endif

                    <div class="mb-3">
                        <label class="form-label" for="titre">Titre affiché</label>
                        <input class="form-control" id="titre" name="titre" value="{{ $titre }}" required maxlength="200">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="serie_id">Ajouter comme…</label>
                        <select class="form-select" id="serie_id" name="serie_id">
                            <option value="">Nouveau programme (film, documentaire…)</option>
                            @foreach ($series as $s)
                                <option value="{{ $s->id }}" @selected(old('serie_id') == $s->id)>Épisode de « {{ $s->titre }} »</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label" for="univers">Univers</label>
                            <select class="form-select" id="univers" name="univers">
                                <option value="">— choisir —</option>
                                @foreach (config('gade.univers') as $slug => $u)
                                    <option value="{{ $slug }}" @selected($univers === $slug)>{{ $u['label'] }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label" for="duree">Durée</label>
                            <input class="form-control @error('duree') is-invalid @enderror" id="duree" name="duree" value="{{ old('duree', $proposition?->duree_secondes ? gmdate('G:i:s', $proposition->duree_secondes) : '') }}" placeholder="1:32:10">
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label" for="annee">Année</label>
                            <input class="form-control" id="annee" name="annee" type="number" min="1890" max="2100" value="{{ old('annee') }}">
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label" for="saison">Saison</label>
                            <input class="form-control" id="saison" name="saison" type="number" min="1" value="{{ old('saison', 1) }}">
                        </div>
                        <div class="col-6 col-sm-3">
                            <label class="form-label" for="numero">N° d’épisode</label>
                            <input class="form-control" id="numero" name="numero" type="number" min="1" value="{{ old('numero') }}">
                        </div>
                    </div>

                    <fieldset class="mb-3">
                        <legend class="form-label fs-6">Genres</legend>
                        <div class="genres-cases">
                            @foreach (config('gade.genres') as $slug => $g)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="genres[]" value="{{ $slug }}" id="g-{{ $slug }}" @checked(in_array($slug, $genresChoisis))>
                                    <label class="form-check-label" for="g-{{ $slug }}">{{ $g }}</label>
                                </div>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mb-3">
                        <label class="form-label" for="description">Courte description (rédigée par nous, pas recopiée)</label>
                        <textarea class="form-control" id="description" name="description" rows="3" maxlength="2000">{{ old('description', $proposition?->description) }}</textarea>
                    </div>

                    <div class="d-flex flex-wrap gap-4 mb-3">
                        <div class="form-check form-switch">
                            <input type="hidden" name="tout_public" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="tout_public" name="tout_public" value="1" @checked(old('tout_public', $proposition?->tout_public))>
                            <label class="form-check-label" for="tout_public">Tout public (rangée « En famille »)</label>
                        </div>
                        <div class="form-check form-switch">
                            <input type="hidden" name="a_la_une" value="0">
                            <input class="form-check-input" type="checkbox" role="switch" id="a_la_une" name="a_la_une" value="1" @checked(old('a_la_une'))>
                            <label class="form-check-label" for="a_la_une">Mettre à la une</label>
                        </div>
                    </div>

                    <button class="btn btn-gade" type="submit">Ajouter à la sélection</button>
                </form>
            </div>
        </div>
    </div>
@endsection
