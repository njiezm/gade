{{-- Carte programme : $p (Programme), $lazy (bool) --}}
@php
    $genres = $p->genresLabels();
    $meta = $p->estSerie() ? $p->dureeLabel() : collect([$p->annee, reset($genres) ?: null])->filter()->implode(' · ');
@endphp
<a class="carte" href="{{ $p->urlFiche() }}">
    <div class="carte-image">
        <img src="{{ $p->vignetteUrl() }}" alt="" width="480" height="270" @if ($lazy ?? true) loading="lazy" @endif decoding="async">
        <span class="carte-badges">
            @if ($p->estSerie())<span class="badge-carte serie">Série</span>@endif
            @if ($p->tout_public)<span class="badge-carte famille">Tout public</span>@endif
        </span>
        @unless ($p->estSerie())<span class="carte-duree">{{ $p->dureeLabel() }}</span>@endunless
        <span class="carte-lecture" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 4.5v15a1 1 0 0 0 1.5.86l12.5-7.5a1 1 0 0 0 0-1.72L8.5 3.64A1 1 0 0 0 7 4.5z" fill="currentColor"/></svg></span>
    </div>
    <h3 class="carte-titre">{{ $p->titre }}</h3>
    <p class="carte-meta">{{ $meta ?: $p->universLabel() }}</p>
</a>
