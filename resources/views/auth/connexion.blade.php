@extends('layouts.app')

@section('title', 'Connexion')
@section('body_class', 'page-acces')

@section('content')
    <section class="acces">
        <div class="acces-carte acces-formulaire">
            <span class="hand text-jaune">Bonswa lakay !</span>
            <h1 class="h2 mb-3">Connexion</h1>
            <form method="post" action="{{ route('connexion.envoyer') }}" novalidate>
                @csrf
                <div class="mb-3 text-start">
                    <label class="form-label" for="email">E-mail</label>
                    <input class="form-control @error('email') is-invalid @enderror" type="email" id="email" name="email" value="{{ old('email') }}" required autocomplete="username" autofocus inputmode="email">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3 text-start">
                    <label class="form-label" for="password">Mot de passe</label>
                    <input class="form-control @error('password') is-invalid @enderror" type="password" id="password" name="password" required autocomplete="current-password">
                    @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="form-check text-start mb-3">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember" value="1" @checked(old('remember'))>
                    <label class="form-check-label" for="remember">Rester connecté</label>
                </div>
                <button class="btn btn-gade w-100" type="submit">Entrer dans la salle</button>
            </form>
            <p class="mt-3 mb-0 small"><a href="{{ route('mot-de-passe.oublie') }}">Mot de passe oublié ?</a></p>
        </div>
    </section>
@endsection
