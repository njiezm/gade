@extends('layouts.app')

@section('title', 'Page introuvable')
@section('noindex', true)

@section('content')
    <div class="entete-page text-center py-5">
        <span class="hand">Woy… pa ni ayen la.</span>
        <h1>Cette page n’existe pas (ou plus)</h1>
        <p class="mx-auto">Le programme a peut-être été retiré par son ayant droit. Il reste plein de choses à gadé !</p>
        <a class="btn btn-gade mt-3" href="{{ route('accueil') }}">Retour à l’accueil</a>
    </div>
@endsection
