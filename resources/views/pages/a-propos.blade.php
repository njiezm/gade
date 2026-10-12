@extends('layouts.app')

@section('title', 'Comment ça marche & sources')
@section('description', 'GADÉ est une sélection de vidéos publiées gratuitement sur YouTube par leurs ayants droit. Voici comment elle est construite et la liste des chaînes officielles utilisées.')

@section('content')
    <header class="entete-page">
        <span class="hand">Sinéma lakay, mode d’emploi</span>
        <h1>Comment ça marche</h1>
        <p>GADÉ rassemble, dans une interface confortable, des films, séries et documentaires en français que leurs ayants droit publient eux-mêmes gratuitement sur YouTube.</p>
    </header>

    <div class="texte-page">
        <h2>Une sélection, pas un service de streaming</h2>
        <p>GADÉ n’héberge aucune vidéo et n’en télécharge aucune. Chaque programme est lu avec le lecteur intégré de YouTube, en mode confidentialité renforcée (youtube-nocookie). Les vidéos restent la propriété de leurs ayants droit : si l’un d’eux retire une vidéo ou la rend non intégrable, elle disparaît de GADÉ lors de la vérification suivante.</p>

        <h2>Nos règles de choix</h2>
        <ul>
            <li><strong>Uniquement des chaînes officielles</strong> : diffuseurs (ARTE, France Télévisions, réseau La 1ère, TV5MONDE, INA, LCP…), producteurs et distributeurs qui publient leurs propres œuvres, institutions et auteurs qui diffusent leur travail.</li>
            <li><strong>Aucune réupload douteuse</strong> : les chaînes anonymes « film complet » sont écartées. Dans le doute, la vidéo n’entre pas.</li>
            <li><strong>Des programmes longs et complets</strong> : films entiers, épisodes complets, documentaires de plus de 20 minutes. Pas d’extraits, pas de bandes-annonces.</li>
            <li><strong>En français</strong>, et vérifiés un par un : la vidéo doit être intégrable (contrôle oEmbed de YouTube).</li>
            <li>Certains diffuseurs, comme ARTE, annoncent une date de fin de mise en ligne : elle est indiquée sur la fiche quand elle approche.</li>
        </ul>

        <h2>Kontinyé gadé &amp; Ma liste</h2>
        <p>Chaque profil a sa propre liste, ses positions de lecture (« Kontinyé gadé ») et son historique (« Déjà vu »), enregistrés dans GADÉ : on les retrouve sur tous les appareils de la maison. Seule N’jie voit le résumé de l’activité de chaque profil.</p>

        <h2>Les chaînes officielles de la sélection</h2>
        <ul class="tableau-sources">
            @foreach ($chaines as $nom => $c)
                <li>
                    <a href="{{ $c['url'] }}" target="_blank" rel="noopener">{{ $nom }} ↗</a>
                    <span>{{ $c['n'] }} programme{{ $c['n'] > 1 ? 's' : '' }}</span>
                </li>
            @endforeach
        </ul>

        <h2>Un souci avec une vidéo ?</h2>
        <p>Une vidéo qui ne se lance plus a sans doute été retirée par son ayant droit : elle sera masquée automatiquement à la prochaine vérification. Vous êtes ayant droit et souhaitez qu’un programme soit retiré de la sélection ? Il suffit de nous l’écrire via <a href="https://www.njiezm.fr" rel="noopener">njiezm.fr</a>.</p>
    </div>
@endsection
