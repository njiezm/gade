@extends('layouts.app')

@section('title', 'Gérer les profils')
@section('body_class', 'page-profils')

@section('content')
    <section class="profils" aria-labelledby="titre-gerer">
        <h1 class="profils-titre" id="titre-gerer">Gérer les profils</h1>
        <p class="profils-sous-titre">Touchez un profil pour le modifier ({{ $profils->count() }} / {{ $max }}).</p>

        <ul class="profils-grille">
            @foreach ($profils as $p)
                <li>
                    <a class="profil-choix is-edition" href="{{ route('profils.modifier', $p) }}" style="--accent: {{ $p->couleur }}">
                        @include('partials.avatar', ['profil' => $p, 'taille' => 'lg'])
                        <span class="profil-crayon" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M4 20h4L19 9l-4-4L4 16z M14 6l4 4" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/></svg></span>
                        <span class="profil-prenom">{{ $p->prenom }}<span class="visually-hidden"> : modifier</span></span>
                        @if ($p->gestionnaire)<span class="profil-badge is-mere">Gestionnaire</span>@elseif ($p->enfant)<span class="profil-badge">Enfant</span>@endif
                    </a>
                </li>
            @endforeach
            @if ($profils->count() < $max)
                <li>
                    <a class="profil-choix is-ajout" href="{{ route('profils.creer') }}">
                        <span class="avatar avatar-lg avatar-ajout" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg></span>
                        <span class="profil-prenom">Ajouter un profil</span>
                    </a>
                </li>
            @endif
        </ul>

        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a class="btn btn-gade" href="{{ route('accueil') }}">Terminé</a>
            <a class="btn btn-velours" href="{{ route('gestion') }}">Activité des profils</a>
        </div>
    </section>
@endsection
