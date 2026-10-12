{{-- Avatar rond d'un profil : $profil, $taille (classe : sm, md, lg), $alt --}}
<span class="avatar avatar-{{ $taille ?? 'md' }}" style="--accent: {{ $profil->couleur }}">
    <img src="{{ $profil->avatarUrl() }}" alt="{{ $alt ?? '' }}" width="200" height="200" @if (($taille ?? 'md') === 'sm') loading="lazy" @endif>
</span>
