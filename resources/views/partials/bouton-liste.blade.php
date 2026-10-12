@php $dans = $dans ?? (\App\Models\Profil::actif()?->aDansSaListe($programme) ?? false); @endphp
<button class="btn btn-velours {{ ($grand ?? false) ? 'btn-lg' : '' }}" type="button" data-liste="{{ $programme->slug }}" aria-pressed="{{ $dans ? 'true' : 'false' }}">
    <svg viewBox="0 0 24 24" aria-hidden="true" data-liste-icone>@if ($dans)<path d="M5 12.5l4.2 4.2L19 7" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"/>@else<path d="M12 5v14M5 12h14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"/>@endif</svg>
    <span data-liste-label>{{ $dans ? 'Dans ma liste' : 'Ma liste' }}</span>
</button>
