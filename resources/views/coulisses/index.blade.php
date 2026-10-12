@extends('layouts.app')

@section('title', 'Coulisses')
@section('noindex', true)

@section('content')
    <div class="coulisses">
        <div class="coulisses-entete">
            <div>
                <span class="hand">Dèyè rido-a</span>
                <h1>Les coulisses de GADÉ</h1>
            </div>
            <div class="d-flex flex-wrap gap-2"><a class="btn btn-velours" href="{{ route('gestion') }}">Gestion</a><form method="post" action="{{ route('coulisses.deconnexion') }}">@csrf<button class="btn btn-velours" type="submit">Quitter les coulisses</button></form></div>
        </div>

        @if ($errors->any())
            <div class="flash erreur" role="alert" data-flash>{{ $errors->first() }}</div>
        @endif

        <div class="chiffres">
            <div><strong>{{ $stats['visibles'] }}</strong><span>programmes visibles</span></div>
            <div><strong>{{ $stats['episodes'] }}</strong><span>épisodes</span></div>
            <div><strong>{{ $stats['une'] }}</strong><span>à la une</span></div>
            <div><strong>{{ $stats['masques'] }}</strong><span>masqués</span></div>
            <div><strong>{{ $propositions->count() }}</strong><span>propositions à valider</span></div>
        </div>

        <div class="row g-4">
            <div class="col-xl-6">
                <section class="panneau" aria-labelledby="t-ajouter">
                    <h2 id="t-ajouter">Ajouter un programme</h2>
                    <p class="text-sable small">Collez l’adresse d’une vidéo YouTube : le titre, la chaîne et la vignette sont récupérés automatiquement (oEmbed). Vous complétez ensuite l’univers, les genres et la durée.</p>
                    <form method="get" action="{{ route('coulisses.ajouter') }}" class="d-flex flex-wrap gap-2">
                        <label class="visually-hidden" for="url">Adresse YouTube</label>
                        <input class="form-control flex-grow-1 w-auto @error('url') is-invalid @enderror" type="url" id="url" name="url" placeholder="https://www.youtube.com/watch?v=…" value="{{ old('url') }}" required>
                        <button class="btn btn-gade" type="submit">Continuer</button>
                    </form>
                </section>
            </div>
            <div class="col-xl-6">
                <section class="panneau" aria-labelledby="t-verif">
                    <h2 id="t-verif">Dernière vérification</h2>
                    @if ($derniere)
                        <p class="small text-sable mb-2">{{ $derniere->created_at->format('d/m/Y à H:i') }} — {{ $derniere->total }} vidéos, {{ $derniere->indisponibles }} indisponible(s), {{ $derniere->erreurs }} sans réponse.</p>
                        <details><summary class="small">Voir le rapport</summary><div class="rapport mt-2">{{ $derniere->rapport }}</div></details>
                    @else
                        <p class="small text-sable">Aucune vérification lancée pour l’instant.</p>
                    @endif
                    <p class="small text-discret mt-2 mb-0">Pour tout revérifier : <code>php artisan gade:verifier</code> (les vidéos devenues indisponibles sont masquées automatiquement).</p>
                </section>
            </div>
        </div>

        <section class="panneau" id="propositions" aria-labelledby="t-props">
            <h2 id="t-props">Propositions à valider</h2>
            <p class="text-sable small">Claude dépose ici ses suggestions (<code>php artisan gade:proposer URL</code>). Vous pouvez aussi en ajouter une pour plus tard.</p>
            <form method="post" action="{{ route('coulisses.proposer') }}" class="row g-2 mb-3">
                @csrf
                <div class="col-md-6"><label class="visually-hidden" for="p-url">Adresse YouTube</label><input class="form-control @error('proposition') is-invalid @enderror" id="p-url" name="url" type="url" placeholder="Adresse YouTube à proposer" required></div>
                <div class="col-md-4"><label class="visually-hidden" for="p-motif">Pourquoi ?</label><input class="form-control" id="p-motif" name="motif" placeholder="Pourquoi ? (facultatif)"></div>
                <div class="col-md-2"><button class="btn btn-velours w-100" type="submit">Proposer</button></div>
            </form>
            @if ($propositions->isEmpty())
                <p class="vide mb-0"><span class="hand">Tout est à jour !</span>Aucune proposition en attente.</p>
            @else
                <ul class="liste-coulisses">
                    @foreach ($propositions as $pr)
                        <li class="proposition">
                            <img src="{{ $pr->vignette() }}" alt="" width="120" height="68" loading="lazy">
                            <div>
                                <span class="titre-ligne">{{ $pr->titre }}</span>
                                <span class="meta-ligne">{{ $pr->chaine_nom }} · {{ $pr->dureeLabel() }} · {{ $pr->univers ? config('gade.univers.'.$pr->univers.'.label') : 'univers à choisir' }} · proposé par {{ $pr->source === 'claude' ? 'Claude' : ($pr->source === 'cli' ? 'la ligne de commande' : 'les coulisses') }}</span>
                                @if ($pr->motif)<span class="motif">{{ $pr->motif }}</span>@endif
                            </div>
                            <div class="actions-ligne">
                                <a class="btn btn-velours" href="{{ $pr->url }}" target="_blank" rel="noopener">Voir sur YouTube ↗</a>
                                <a class="btn btn-gade" href="{{ route('coulisses.ajouter', ['proposition' => $pr->id]) }}">Valider…</a>
                                <form method="post" action="{{ route('coulisses.refuser', $pr) }}">@csrf<button class="btn btn-rouge" type="submit">Refuser</button></form>
                            </div>
                        </li>
                    @endforeach
                </ul>
            @endif
            @if ($historique->isNotEmpty())
                <details class="mt-3"><summary class="small text-sable">Dernières décisions</summary>
                    <ul class="small text-sable mt-2 mb-0">
                        @foreach ($historique as $h)
                            <li>{{ $h->statut === 'validee' ? '✓ Validée' : '✗ Refusée' }} — {{ $h->titre }} ({{ $h->traitee_le?->format('d/m/Y') }})</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        </section>

        <section class="panneau" aria-labelledby="t-selection">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <h2 id="t-selection" class="m-0">La sélection</h2>
                <nav class="puces-univers p-0 m-0" aria-label="Filtrer la liste">
                    @foreach (['tous' => 'Tous', 'une' => 'À la une', 'series' => 'Séries', 'masques' => 'Masqués'] as $k => $l)
                        <a href="{{ route('coulisses.index', ['voir' => $k]) }}" class="{{ $filtre === $k ? 'is-actif' : '' }}">{{ $l }}</a>
                    @endforeach
                </nav>
            </div>
            <p class="small text-discret">Classer : « Monter » fait remonter le programme dans les rangées et la sélection (ordre plus élevé = plus haut).</p>
            <ul class="liste-coulisses">
                @foreach ($programmes as $p)
                    <li class="ligne-programme {{ $p->masque || $p->statut_verif === 'indisponible' ? 'is-masque' : '' }}">
                        <img src="{{ $p->vignetteUrl() }}" alt="" width="120" height="68" loading="lazy">
                        <div>
                            <span class="titre-ligne">{{ $p->titre }}</span>
                            <span class="meta-ligne">
                                {{ $p->universLabel() }} · {{ $p->estSerie() ? $p->episodes_count.' épisodes' : \App\Support\Duree::court($p->duree_secondes) }} · ordre {{ $p->ordre }}
                                @if ($p->a_la_une) · <span class="text-jaune">à la une</span>@endif
                                @if ($p->tout_public) · tout public @endif
                                @if ($p->masque) · <span class="text-danger">masqué{{ $p->masque_raison ? ' ('.$p->masque_raison.')' : '' }}</span>@endif
                            </span>
                        </div>
                        <div class="actions-ligne">
                            <form method="post" action="{{ route('coulisses.classer', $p) }}">@csrf<input type="hidden" name="sens" value="haut"><button class="btn btn-velours" type="submit" aria-label="Monter {{ $p->titre }}">↑ Monter</button></form>
                            <form method="post" action="{{ route('coulisses.classer', $p) }}">@csrf<input type="hidden" name="sens" value="bas"><button class="btn btn-velours" type="submit" aria-label="Descendre {{ $p->titre }}">↓</button></form>
                            <form method="post" action="{{ route('coulisses.une', $p) }}">@csrf<button class="btn {{ $p->a_la_une ? 'btn-gade' : 'btn-velours' }}" type="submit">{{ $p->a_la_une ? '★ À la une' : '☆ Une' }}</button></form>
                            <form method="post" action="{{ route('coulisses.masque', $p) }}">@csrf<button class="btn btn-velours" type="submit">{{ $p->masque ? 'Réafficher' : 'Masquer' }}</button></form>
                            <form method="post" action="{{ route('coulisses.verifier', $p) }}">@csrf<button class="btn btn-velours" type="submit">Vérifier</button></form>
                            <a class="btn btn-velours" href="{{ route('coulisses.modifier', $p) }}">Modifier</a>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    </div>
@endsection
