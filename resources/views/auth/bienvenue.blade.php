@extends('layouts.app')

@section('title', 'Accès famille')
@section('description', 'GADÉ — Sinéma lakay, accès famille.')
@section('body_class', 'page-acces')

@section('content')
    <section class="acces">
        <div class="acces-carte">
            @include('partials.signe', ['class' => 'acces-signe', 'id' => 'acces'])
            <h1 class="acces-titre">GADÉ <span>Sinéma lakay</span></h1>
            <p class="hand acces-creole">Accès famille</p>
            <p class="acces-texte">Le petit cinéma de la maison est réservé à la famille. Connectez-vous pour choisir votre profil.</p>
            <a class="btn btn-gade btn-lg" href="{{ route('connexion') }}">Se connecter</a>
        </div>
    </section>
@endsection
