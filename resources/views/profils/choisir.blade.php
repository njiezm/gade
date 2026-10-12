@extends('layouts.app')

@section('title', 'Qui regarde ?')
@section('body_class', 'page-profils')

@section('content')
    <section class="profils" aria-labelledby="titre-profils">
        <h1 class="profils-titre" id="titre-profils">Ki moun ka gadé ?</h1>
        <p class="profils-sous-titre">Qui regarde ?</p>

        <ul class="profils-grille">
            @foreach ($profils as $p)
                <li>
                    <form method="post" action="{{ route('profils.choisir', $p) }}">
                        @csrf
                        <button class="profil-choix" type="submit" style="--accent: {{ $p->couleur }}">
                            @include('partials.avatar', ['profil' => $p, 'taille' => 'lg'])
                            <span class="profil-prenom">{{ $p->prenom }}</span>
                            @if ($p->aUnPin())
                                <span class="profil-cadenas" title="Protégé par un code PIN"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="5" y="10.5" width="14" height="10" rx="2.5" fill="currentColor"/><path d="M8 10.5V8a4 4 0 0 1 8 0v2.5" fill="none" stroke="currentColor" stroke-width="2.2"/></svg><span class="visually-hidden"> (code PIN)</span></span>
                            @endif
                            @if ($p->enfant)<span class="profil-badge">Enfant</span>@endif
                        </button>
                    </form>
                </li>
            @endforeach
        </ul>

        @if ($gestionnaire)
            <form method="post" action="{{ route('profils.choisir', $gestionnaire) }}">
                @csrf
                <input type="hidden" name="suite" value="gerer">
                <button class="btn btn-contour" type="submit">Gérer les profils</button>
            </form>
            <p class="profils-aide">« Gérer les profils » passe par le profil de {{ $gestionnaire->prenom }}.</p>
        @endif
    </section>
@endsection
