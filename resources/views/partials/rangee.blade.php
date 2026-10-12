{{-- Rangée horizontale : $id, $titre, $sous_titre, $lien, $items, $madras --}}
<section class="rangee {{ ($madras ?? false) ? 'is-madras' : '' }} {{ ! empty($theme) ? 'is-'.$theme : '' }}" data-rangee aria-labelledby="rangee-{{ $id }}">
    <div class="rangee-entete">
        <div>
            <h2 class="rangee-titre" id="rangee-{{ $id }}">@if (($theme ?? null) === 'richesse')<svg class="rangee-icone" viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="12" r="9" fill="none" stroke="currentColor" stroke-width="2"/><path d="M12 6.5v11M14.8 8.8c-.6-.8-1.6-1.2-2.8-1.2-1.6 0-2.8.8-2.8 2.1 0 3 5.8 1.6 5.8 4.6 0 1.3-1.2 2.2-3 2.2-1.3 0-2.4-.5-3-1.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>@endif{{ $titre }}</h2>
            @if (! empty($sous_titre))<p class="rangee-sous-titre">{{ $sous_titre }}</p>@endif
            @if (! empty($accroche))<p class="rangee-accroche"><span class="hand" lang="gcf">{{ $accroche }}</span> <span class="rangee-accroche-fr">{{ $accroche_fr ?? '' }}</span></p>@endif
        </div>
        <div class="rangee-outils">
            @if (! empty($lien))<a class="tout-voir" href="{{ $lien }}">Tout voir<span class="visually-hidden"> : {{ $titre }}</span> →</a>@endif
            <button class="fleche" type="button" data-fleche="prec" aria-label="Défiler vers la gauche : {{ $titre }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 5l-7 7 7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
            <button class="fleche" type="button" data-fleche="suiv" aria-label="Défiler vers la droite : {{ $titre }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 5l7 7-7 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/></svg></button>
        </div>
    </div>
    <ul class="piste" data-piste tabindex="0" aria-label="{{ $titre }} — faites défiler avec les flèches du clavier ou au doigt">
        @foreach ($items as $p)
            @if ($reprises ?? false)
                <li>@include('partials.carte-reprise', ['r' => $p])</li>
            @else
                <li>@include('partials.carte', ['p' => $p, 'lazy' => ! ($eager ?? false) || $loop->index > 5])</li>
            @endif
        @endforeach
    </ul>
</section>
