@extends('layouts.app')

@section('title', $titre)
@section('body_class', 'page-profils')

@section('content')
    <section class="profils profils-pin" aria-labelledby="titre-pin">
        @include('partials.avatar', ['profil' => $profil, 'taille' => 'lg'])
        <h1 class="profils-titre h2 mt-3" id="titre-pin">{{ $titre }}</h1>
        <p class="profils-sous-titre">{{ $sous_titre ?? 'Saisissez le code à 4 chiffres pour entrer.' }}</p>
        <form method="post" action="{{ $action }}" class="pin-formulaire" novalidate>
            @csrf
            <label class="visually-hidden" for="pin">Code PIN à 4 chiffres</label>
            <input class="pin-champ @error('pin') is-invalid @enderror" id="pin" name="pin" type="password" inputmode="numeric" pattern="[0-9]{4}" maxlength="4" autocomplete="off" required autofocus data-pin data-pin-auto aria-describedby="pin-erreur">
            <div id="pin-erreur" class="pin-erreur" role="alert">@error('pin'){{ $message }}@enderror</div>
            <div class="d-flex gap-2 justify-content-center mt-3">
                <button class="btn btn-gade" type="submit">Valider</button>
                <a class="btn btn-velours" href="{{ $retour }}">Annuler</a>
            </div>
        </form>
        <p class="profils-aide mt-4">Code oublié ? Après {{ config('gade.profils.pin_essais') }} erreurs, le profil est bloqué {{ config('gade.profils.pin_blocage_minutes') }} min. Le code peut être retiré en ligne de commande : <code>php artisan gade:pin</code>.</p>
    </section>
@endsection
