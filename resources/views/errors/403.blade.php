@extends('layouts.app')

@section('title', 'Accès réservé')

@section('content')
    <div class="entete-page text-center py-5">
        <span class="hand">Sa sé pou manman…</span>
        <h1>Accès réservé</h1>
        <p class="mx-auto">{{ $exception->getMessage() ?: 'Cette partie de GADÉ est réservée au profil de N’jie.' }}</p>
        <a class="btn btn-gade mt-3" href="{{ route('accueil') }}">Retour à l’accueil</a>
    </div>
@endsection
