{{-- Carte « Kontinyé gadé » : $r = [p, url, vignette, meta, pourcent] --}}
<a class="carte" href="{{ $r['url'] }}">
    <div class="carte-image">
        <img src="{{ $r['vignette'] }}" alt="" width="480" height="270" loading="lazy" decoding="async">
        <span class="carte-badges">
            @if ($r['p']->estSerie())<span class="badge-carte serie">Série</span>@endif
            @if ($r['p']->tout_public)<span class="badge-carte famille">Tout public</span>@endif
        </span>
        <span class="carte-lecture" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M7 4.5v15a1 1 0 0 0 1.5.86l12.5-7.5a1 1 0 0 0 0-1.72L8.5 3.64A1 1 0 0 0 7 4.5z" fill="currentColor"/></svg></span>
        @if ($r['pourcent'] > 0)
            <span class="carte-progression" aria-hidden="true"><span style="width: {{ $r['pourcent'] }}%"></span></span>
        @endif
    </div>
    <h3 class="carte-titre">{{ $r['p']->titre }}</h3>
    <p class="carte-meta">{{ $r['meta'] }}</p>
</a>
