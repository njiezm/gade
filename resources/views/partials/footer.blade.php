<footer class="pied">
    <div class="madras" aria-hidden="true"></div>
    <div class="pied-interieur">
        <div>
            <img class="pied-logo" src="{{ asset('images/brand/logo-gade.svg') }}" alt="GADÉ — Sinéma lakay" width="190" height="61" loading="lazy">
            <p class="hand text-jaune mt-2 mb-0">Bon film lakay ou !</p>
        </div>
        <div class="pied-avertissement">
            <p class="mb-2"><strong>GADÉ n’est qu’une sélection.</strong> Toutes les vidéos sont hébergées sur YouTube et appartiennent à leurs ayants droit : chaînes officielles de diffuseurs, producteurs et institutions, citées sur chaque fiche avec un lien vers leur chaîne.</p>
            <p class="mb-0">GADÉ n’héberge, ne télécharge et ne copie aucune vidéo : la lecture passe uniquement par le lecteur intégré de YouTube (mode confidentialité renforcée, youtube-nocookie). Une vidéo retirée par son ayant droit disparaît de la sélection.</p>
        </div>
        <ul class="pied-liens">
            @isset($profilActif)
                <li><a href="{{ route('a-propos') }}">Comment ça marche &amp; sources</a></li>
                <li><a href="{{ route('parcourir') }}">Toute la sélection</a></li>
                <li><a href="{{ route('ma-liste') }}">Ma liste</a></li>
            @endisset
            <li><a href="{{ route('mentions') }}">Mentions légales &amp; confidentialité</a></li>
            @if (isset($profilActif) && $profilActif->gestionnaire)
                <li><a href="{{ route('coulisses.index') }}" rel="nofollow">Coulisses</a></li>
            @endif
        </ul>
    </div>
    <div class="pied-bas">
        <span>© {{ date('Y') }} GADÉ — Sinéma lakay · Le Robert, Martinique</span>
        <span>Concept réalisé par <a href="https://www.njiezm.fr" rel="noopener">NJIEZM.FR — www.njiezm.fr</a></span>
    </div>
</footer>
