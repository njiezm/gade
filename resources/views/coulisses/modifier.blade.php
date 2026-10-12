@extends('layouts.app')

@section('title', 'Modifier — Coulisses')
@section('noindex', true)

@section('content')
    <div class="coulisses">
        <div class="coulisses-entete">
            <div>
                <a class="small" href="{{ route('coulisses.index') }}">← Retour aux coulisses</a>
                <h1>{{ $p->titre }}</h1>
            </div>
            <a class="btn btn-velours" href="{{ $p->urlFiche() }}" target="_blank">Voir la fiche ↗</a>
        </div>

        <form class="panneau" method="post" action="{{ route('coulisses.mettre-a-jour', $p) }}">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label" for="titre">Titre</label>
                    <input class="form-control" id="titre" name="titre" value="{{ old('titre', $p->titre) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label" for="univers">Univers</label>
                    <select class="form-select" id="univers" name="univers">
                        @foreach (config('gade.univers') as $slug => $u)
                            <option value="{{ $slug }}" @selected($p->univers === $slug)>{{ $u['label'] }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-4">
                    <label class="form-label" for="duree">Durée{{ $p->estSerie() ? ' (épisode type)' : '' }}</label>
                    <input class="form-control" id="duree" name="duree" value="{{ $p->duree_secondes ? gmdate('G:i:s', $p->duree_secondes) : '' }}">
                </div>
                <div class="col-4">
                    <label class="form-label" for="annee">Année</label>
                    <input class="form-control" id="annee" name="annee" type="number" value="{{ $p->annee }}">
                </div>
                <div class="col-4">
                    <label class="form-label" for="ordre">Ordre</label>
                    <input class="form-control" id="ordre" name="ordre" type="number" value="{{ $p->ordre }}">
                </div>
                <fieldset class="col-12">
                    <legend class="form-label fs-6">Genres</legend>
                    <div class="genres-cases">
                        @foreach (config('gade.genres') as $slug => $g)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="genres[]" value="{{ $slug }}" id="g-{{ $slug }}" @checked(in_array($slug, $p->genres ?? []))>
                                <label class="form-check-label" for="g-{{ $slug }}">{{ $g }}</label>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
                <div class="col-12">
                    <label class="form-label" for="description">Description</label>
                    <textarea class="form-control" id="description" name="description" rows="4">{{ $p->description }}</textarea>
                </div>
                <div class="col-12 d-flex flex-wrap gap-4">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="tout_public" name="tout_public" value="1" @checked($p->tout_public)><label class="form-check-label" for="tout_public">Tout public</label></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="a_la_une" name="a_la_une" value="1" @checked($p->a_la_une)><label class="form-check-label" for="a_la_une">À la une</label></div>
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="masque" name="masque" value="1" @checked($p->masque)><label class="form-check-label" for="masque">Masqué</label></div>
                </div>
                <div class="col-md-8">
                    <label class="form-label" for="masque_raison">Raison du masquage (facultatif)</label>
                    <input class="form-control" id="masque_raison" name="masque_raison" value="{{ $p->masque_raison }}">
                </div>
            </div>
            <button class="btn btn-gade mt-3" type="submit">Enregistrer</button>
        </form>

        @if ($p->estSerie())
            <section class="panneau">
                <h2>Épisodes ({{ $p->episodes->count() }})</h2>
                <p class="small text-sable">Pour ajouter un épisode : « Ajouter un programme » dans les coulisses, puis « Épisode de {{ $p->titre }} ».</p>
                <ol class="small text-sable mb-0">
                    @foreach ($p->episodes as $e)
                        <li>{{ $e->etiquette() }} — {{ $e->titre }} ({{ $e->dureeLabel() }}){{ $e->statut_verif === 'indisponible' ? ' — indisponible' : '' }}</li>
                    @endforeach
                </ol>
            </section>
        @endif
    </div>
@endsection
