{{-- « Sur la télé » : $video (ID YouTube), $slug, $episode (ID ou null), $contexte ('lecteur'|'fiche'), $lecture (URL du lecteur) --}}
@php $uid = 'tele-'.substr(md5(($slug ?? '').($contexte ?? '')), 0, 6); @endphp
<button class="btn btn-velours" type="button" data-tele-ouvrir="{{ $uid }}" data-tv-focus>
    <span aria-hidden="true">📺</span> Sur la télé
</button>
<dialog class="tele-panneau" id="{{ $uid }}" data-tele
        data-video="{{ $video }}" data-slug="{{ $slug }}" data-episode="{{ $episode ?? '' }}"
        data-contexte="{{ $contexte ?? 'fiche' }}" data-lecture="{{ $lecture ?? '' }}"
        aria-labelledby="{{ $uid }}-titre">
    <div class="tele-tete">
        <h2 id="{{ $uid }}-titre"><span aria-hidden="true">📺</span> Sur la télé</h2>
        <button class="icone-btn" type="button" data-tele-fermer aria-label="Fermer">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/></svg>
        </button>
    </div>

    {{-- Téléphone ou tablette (Android, iPhone, iPad) --}}
    <section class="tele-bloc" data-tele-plateforme="mobile">
        <a class="btn btn-gade btn-lg w-100" href="https://www.youtube.com/watch?v={{ $video }}" data-tele-youtube target="_blank" rel="noopener">
            Ouvrir dans l’app YouTube puis caster
        </a>
        <p class="tele-aide">La vidéo s’ouvre <strong data-tele-temps>au même moment</strong>. Dans YouTube, touchez l’icône <strong>Cast</strong> (écran avec ondes) et choisissez votre TV ou Chromecast.</p>
        <p class="tele-aide">Si l’icône Cast apparaît directement dans le lecteur GADÉ, touchez-la.</p>
    </section>

    {{-- Ordinateur (Chrome, Edge) --}}
    <section class="tele-bloc" data-tele-plateforme="ordi">
        <p class="tele-aide"><strong>Chrome ou Edge :</strong> menu <kbd>⋮</kbd> → <strong>Caster…</strong> (Edge : <strong>Caster le média sur l’appareil</strong>) → <strong>Sources</strong> → <strong>Caster l’onglet</strong>, puis :</p>
        <button class="btn btn-gade btn-lg w-100" type="button" data-tele-mode-tv>Mode TV plein écran</button>
        <a class="tele-lien" href="https://www.youtube.com/watch?v={{ $video }}" data-tele-youtube target="_blank" rel="noopener">Ou ouvrir sur youtube.com ↗</a>
    </section>

    {{-- Toutes plateformes --}}
    <section class="tele-bloc tele-autres">
        <p class="tele-aide"><strong>Sur une Smart TV :</strong> ouvrez <strong>{{ parse_url(config('app.url'), PHP_URL_HOST) ?: 'gade.test' }}</strong> (bientôt l’adresse en ligne) dans le navigateur de la TV, puis activez le <strong>Mode TV</strong> dans le menu (télécommande : flèches, OK, Retour).</p>
        <p class="tele-aide"><strong>App YouTube de la TV :</strong> Paramètres → <strong>Associer à la TV avec un code</strong>, puis entrez le code dans l’app YouTube du téléphone : la TV devient un écran de lecture.</p>
    </section>
</dialog>
