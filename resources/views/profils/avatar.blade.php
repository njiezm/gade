@extends('layouts.app')

@section('title', 'Choisir un avatar')
@section('body_class', 'page-profils')

@section('content')
    <form method="post" action="{{ route('profils.avatar.changer', $profil) }}" class="avatars" aria-labelledby="titre-avatar">
        @csrf
        @method('PUT')
        <div class="avatars-entete">
            <div class="avatars-apercu" style="--accent: {{ $profil->couleur }}">
                <span class="avatar avatar-lg"><img src="{{ $profil->avatarUrl() }}" alt="" width="200" height="200" data-apercu-avatar></span>
            </div>
            <div class="avatars-texte">
                <span class="hand text-jaune">Chwazi tèt ou !</span>
                <h1 class="h2 mb-1" id="titre-avatar">Choisir un avatar</h1>
                <p class="mb-2 text-sable">Pour <strong class="text-creme">{{ $profil->prenom }}</strong> · <span data-apercu-nom>{{ \App\Support\Avatars::nom($profil->avatar) }}</span></p>
                <div class="d-flex flex-wrap gap-2">
                    <button class="btn btn-gade" type="submit">Enregistrer</button>
                    <a class="btn btn-velours" href="{{ \App\Models\Profil::actif()?->gestionnaire && ! \App\Models\Profil::actif()->is($profil) ? route('profils.gerer') : route('accueil') }}">Annuler</a>
                </div>
            </div>
        </div>

        <div data-galerie-avatars>
            @foreach ($themes as $t)
                <fieldset class="avatars-theme">
                    <legend><span class="avatars-theme-titre">{{ $t['label'] }}</span> <span class="avatars-theme-texte">{{ $t['texte'] }}</span></legend>
                    <div class="avatars-grille">
                        @foreach ($t['avatars'] as $a)
                            <label class="avatar-option" title="{{ $a['nom'] }}">
                                <input type="radio" name="avatar" value="{{ $a['cle'] }}" @checked($profil->avatar === $a['cle']) data-src="{{ asset('images/avatars/'.$a['cle'].'.svg') }}" data-nom="{{ $a['nom'] }}">
                                <img src="{{ asset('images/avatars/'.$a['cle'].'.svg') }}" alt="" width="200" height="200" loading="lazy">
                                <span class="avatar-option-nom">{{ $a['nom'] }}</span>
                            </label>
                        @endforeach
                    </div>
                </fieldset>
            @endforeach
        </div>

        <div class="avatars-pied">
            <button class="btn btn-gade btn-lg" type="submit">Enregistrer cet avatar</button>
        </div>
    </form>
@endsection
