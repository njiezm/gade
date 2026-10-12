{{-- Menu du profil actif (en-tête) : changer de profil, avatar, gérer (N'jie), compte, déconnexion --}}
<div class="menu-profil">
    <button class="menu-profil-btn" type="button" data-menu-profil aria-expanded="false" aria-controls="menu-profil-liste" aria-haspopup="true">
        @include('partials.avatar', ['profil' => $profilActif, 'taille' => 'sm'])
        <span class="visually-hidden">Profil de {{ $profilActif->prenom }} : ouvrir le menu</span>
        <svg class="menu-profil-chevron" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 9l6 6 6-6" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>
    <div class="menu-profil-liste" id="menu-profil-liste" hidden>
        <p class="menu-profil-qui">
            <strong>{{ $profilActif->prenom }}</strong>
            <span>{{ $profilActif->gestionnaire ? 'Profil gestionnaire' : ($profilActif->enfant ? 'Profil enfant' : 'Profil') }}</span>
        </p>
        <a href="{{ route('profils') }}">Changer de profil</a>
        <a href="{{ route('profils.avatar', $profilActif) }}">Changer mon avatar</a>
        @if ($profilActif->gestionnaire)
            <a href="{{ route('profils.gerer') }}">Gérer les profils</a>
            <a href="{{ route('gestion') }}">Gestion &amp; activité</a>
            <a href="{{ route('coulisses.index') }}">Coulisses</a>
        @endif
        <a href="{{ route('compte') }}">Compte</a>
        <form method="post" action="{{ route('deconnexion') }}">@csrf<button type="submit">Se déconnecter</button></form>
    </div>
</div>
