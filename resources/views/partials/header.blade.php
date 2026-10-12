@php
    $nav = ! isset($profilActif) ? [] : [
        ['route' => 'accueil', 'label' => 'Accueil', 'params' => [], 'actif' => request()->routeIs('accueil')],
        ['route' => 'parcourir', 'label' => 'Films', 'params' => ['univers' => 'films'], 'actif' => request()->query('univers') === 'films'],
        ['route' => 'parcourir', 'label' => 'Séries', 'params' => ['univers' => 'series'], 'actif' => request()->query('univers') === 'series'],
        ['route' => 'parcourir', 'label' => 'Documentaires', 'params' => ['univers' => 'documentaires'], 'actif' => request()->query('univers') === 'documentaires'],
        ['route' => 'parcourir', 'label' => 'Caraïbes', 'params' => ['univers' => 'caraibes'], 'actif' => request()->query('univers') === 'caraibes'],
        ['route' => 'parcourir', 'label' => 'En famille', 'params' => ['famille' => 1], 'actif' => request()->boolean('famille') && ! request()->query('univers')],
        ['route' => 'ma-liste', 'label' => 'Ma liste', 'params' => [], 'actif' => request()->routeIs('ma-liste')],
    ];
@endphp
<header class="site-header" data-header>
    <nav class="navbar navbar-expand-lg" aria-label="Navigation principale">
        <div class="entete-ligne">
            <a class="marque" href="{{ route('accueil') }}" aria-label="GADÉ, Sinéma lakay — retour à l’accueil">
                @include('partials.signe', ['class' => 'signe', 'id' => 'hd'])
                <span class="marque-texte">
                    @include('partials.mot-gade')
                    <span class="marque-sig">Sinéma lakay</span>
                </span>
            </a>

            @if (! isset($profilActif))
                {{-- En-tête minimal : visiteur, connexion, choix du profil --}}
                <div class="entete-actions is-minimal">
                    @auth
                        <form method="post" action="{{ route('deconnexion') }}">@csrf<button class="btn btn-velours btn-sm" type="submit">Se déconnecter</button></form>
                    @else
                        @unless (request()->routeIs('connexion'))
                            <a class="btn btn-gade btn-sm" href="{{ route('connexion') }}">Se connecter</a>
                        @endunless
                    @endauth
                </div>
            @else
            <div class="entete-actions">
                <form class="recherche-entete" action="{{ route('parcourir') }}" method="get" role="search">
                    <label class="visually-hidden" for="recherche-entete">Rechercher un programme</label>
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    <input id="recherche-entete" type="search" name="q" placeholder="Un titre, une île, un acteur…" value="{{ request()->routeIs('parcourir') ? request('q') : '' }}">
                </form>
                <button class="icone-btn chercher" type="button" data-ouvrir-recherche aria-label="Rechercher">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="11" cy="11" r="7" fill="none" stroke="currentColor" stroke-width="2"/><path d="M20 20l-3.5-3.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                </button>
                @include('partials.menu-profil')
                <button class="navbar-toggler d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#menu-principal" aria-controls="menu-principal" aria-expanded="false" aria-label="Ouvrir le menu">
                    <span class="burger" aria-hidden="true"><span></span><span></span><span></span></span>
                </button>
            </div>

            <div class="collapse navbar-collapse" id="menu-principal">
                <ul class="navbar-nav">
                    @foreach ($nav as $item)
                        <li class="nav-item">
                            <a class="nav-link {{ $item['actif'] ? 'active' : '' }}" href="{{ route($item['route'], $item['params']) }}" @if ($item['actif']) aria-current="page" @endif>{{ $item['label'] }}</a>
                        </li>
                    @endforeach
                </ul>
                <form class="recherche-mobile" action="{{ route('parcourir') }}" method="get" role="search">
                    <label class="visually-hidden" for="recherche-mobile">Rechercher un programme</label>
                    <input id="recherche-mobile" class="form-control" type="search" name="q" placeholder="Ka ou ka chèché ? Un titre, une île…">
                </form>
                @include('partials.menu-pwa')
            </div>
            @endif
        </div>
    </nav>
    <span class="madras-fin" aria-hidden="true"></span>
</header>
