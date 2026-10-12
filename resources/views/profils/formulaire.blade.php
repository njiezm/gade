@extends('layouts.app')

@section('title', $profil->exists ? 'Modifier '.$profil->prenom : 'Ajouter un profil')
@section('body_class', 'page-profils')

@section('content')
    <section class="profil-edition" aria-labelledby="titre-edition">
        <h1 class="profils-titre h2" id="titre-edition">{{ $profil->exists ? 'Modifier le profil' : 'Ajouter un profil' }}</h1>

        <div class="profil-edition-grille">
            <div class="text-center">
                @if ($profil->exists)
                    @include('partials.avatar', ['profil' => $profil, 'taille' => 'lg', 'alt' => 'Avatar actuel'])
                    <div><a class="btn btn-velours btn-sm mt-3" href="{{ route('profils.avatar', $profil) }}">Changer l’avatar</a></div>
                @else
                    <span class="avatar avatar-lg avatar-ajout" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></span>
                    <p class="small text-sable mt-2">L’avatar se choisit juste après.</p>
                @endif
            </div>

            <form method="post" action="{{ $profil->exists ? route('profils.mettre-a-jour', $profil) : route('profils.enregistrer') }}" class="panneau" novalidate>
                @csrf
                @if ($profil->exists)
                    @method('PUT')
                @endif

                <div class="mb-3">
                    <label class="form-label" for="prenom">Prénom</label>
                    <input class="form-control @error('prenom') is-invalid @enderror" id="prenom" name="prenom" value="{{ old('prenom', $profil->prenom) }}" maxlength="20" required autocomplete="off">
                    @error('prenom')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <fieldset class="mb-3">
                    <legend class="form-label">Couleur</legend>
                    <div class="couleurs">
                        @foreach (config('gade.profils.couleurs') as $c)
                            <label class="couleur" style="--c: {{ $c }}">
                                <input type="radio" name="couleur" value="{{ $c }}" @checked(old('couleur', $profil->couleur) === $c)>
                                <span class="visually-hidden">Couleur {{ $c }}</span>
                            </label>
                        @endforeach
                    </div>
                    @error('couleur')<div class="text-danger small">{{ $message }}</div>@enderror
                </fieldset>

                @unless ($profil->gestionnaire)
                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" role="switch" id="enfant" name="enfant" value="1" @checked(old('enfant', $profil->enfant))>
                        <label class="form-check-label" for="enfant"><strong>Profil enfant</strong> : uniquement les programmes « tout public », sans les rangées adultes</label>
                    </div>
                @endunless

                <fieldset class="mb-3">
                    <legend class="form-label">Code PIN (facultatif)</legend>
                    <p class="small text-sable mb-2">
                        @if ($profil->aUnPin())
                            Un code PIN protège ce profil. Laissez vide pour le garder.
                        @else
                            4 chiffres demandés à l’entrée du profil{{ $profil->gestionnaire ? ' et des coulisses (conseillé pour ce profil)' : '' }}.
                        @endif
                    </p>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="visually-hidden" for="pin">Nouveau code PIN</label>
                            <input class="form-control @error('pin') is-invalid @enderror" id="pin" name="pin" type="password" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="Code" autocomplete="new-password" data-pin>
                        </div>
                        <div class="col-6">
                            <label class="visually-hidden" for="pin_confirmation">Confirmer le code PIN</label>
                            <input class="form-control" id="pin_confirmation" name="pin_confirmation" type="password" inputmode="numeric" maxlength="4" pattern="[0-9]{4}" placeholder="Confirmer" autocomplete="new-password" data-pin>
                        </div>
                    </div>
                    @error('pin')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                    @if ($profil->aUnPin())
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="retirer_pin" name="retirer_pin" value="1">
                            <label class="form-check-label" for="retirer_pin">Retirer le code PIN</label>
                        </div>
                    @endif
                </fieldset>

                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-gade" type="submit">Enregistrer</button>
                    <a class="btn btn-velours" href="{{ route('profils.gerer') }}">Annuler</a>
                </div>
            </form>
        </div>

        @if ($profil->exists && ! $profil->gestionnaire)
            <form method="post" action="{{ route('profils.supprimer', $profil) }}" class="text-center mt-4" data-confirmer="Supprimer le profil {{ $profil->prenom }} ? Sa liste et son historique seront effacés.">
                @csrf
                @method('DELETE')
                <button class="btn btn-lien-danger" type="submit">Supprimer ce profil</button>
            </form>
        @endif
    </section>
@endsection
