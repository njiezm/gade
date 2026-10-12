@extends('layouts.app')

@section('title', 'Compte')

@section('content')
    <header class="entete-page">
        <span class="hand">Kont lakay</span>
        <h1>Compte</h1>
        <p>Un seul compte pour toute la famille, et un profil par personne.</p>
    </header>

    <div class="page-wrap compte">
        <div class="panneau">
            <h2>Le compte</h2>
            <dl class="row mb-0">
                <dt class="col-sm-4 text-sable">E-mail de connexion</dt><dd class="col-sm-8 text-break">{{ $user->email }}</dd>
                <dt class="col-sm-4 text-sable">Profil actif</dt>
                <dd class="col-sm-8 d-flex align-items-center gap-2">@include('partials.avatar', ['profil' => $profilActif, 'taille' => 'sm']) {{ $profilActif->prenom }}</dd>
            </dl>
            <div class="d-flex flex-wrap gap-2 mt-3">
                <a class="btn btn-velours" href="{{ route('profils') }}">Changer de profil</a>
                <a class="btn btn-velours" href="{{ route('profils.avatar', $profilActif) }}">Changer mon avatar</a>
                <form method="post" action="{{ route('deconnexion') }}">@csrf<button class="btn btn-velours" type="submit">Se déconnecter</button></form>
            </div>
        </div>

        <div class="panneau">
            <h2>Mot de passe</h2>
            @if ($profilActif->gestionnaire)
                <form method="post" action="{{ route('gestion.mot-de-passe') }}" novalidate class="compte-mdp">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label" for="actuel">Mot de passe actuel</label>
                        <input class="form-control @error('actuel') is-invalid @enderror" type="password" id="actuel" name="actuel" required autocomplete="current-password">
                        @error('actuel')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password">Nouveau mot de passe (10 caractères minimum)</label>
                        <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" required autocomplete="new-password">
                        @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="password_confirmation">Confirmer le nouveau mot de passe</label>
                        <input class="form-control" type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password">
                    </div>
                    <button class="btn btn-gade" type="submit">Changer le mot de passe</button>
                    <p class="small text-sable mt-2 mb-0">Les appareils où « Rester connecté » était coché devront se reconnecter.</p>
                </form>
            @else
                <p class="mb-0 text-sable">Seule N’jie peut changer le mot de passe de la famille. Demandez-lui !</p>
            @endif
        </div>
    </div>
@endsection
