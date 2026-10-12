@extends('layouts.app')

@section('title', 'Mot de passe oublié')
@section('body_class', 'page-acces')

@section('content')
    <section class="acces">
        <div class="acces-carte">
            <span class="hand text-jaune">Pa ni pwoblèm…</span>
            <h1 class="h2 mb-3">Mot de passe oublié ?</h1>
            <p class="acces-texte">GADÉ n’a qu’un seul compte, celui de la famille. Pour retrouver le mot de passe, <strong>contactez N’jie</strong> : elle seule peut le changer.</p>
            <a class="btn btn-velours" href="{{ auth()->check() ? route('profils') : route('connexion') }}">Retour</a>
        </div>
    </section>
@endsection
