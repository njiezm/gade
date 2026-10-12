@extends('layouts.app')

@section('title', 'Mentions légales & confidentialité')
@section('description', 'Mentions légales et politique de confidentialité de GADÉ — Sinéma lakay.')

@section('content')
    <header class="entete-page">
        <h1>Mentions légales &amp; confidentialité</h1>
        <p>GADÉ — Sinéma lakay est un concept de démonstration.</p>
    </header>
    <div class="texte-page">
        <h2>Éditeur</h2>
        <p>Concept conçu et réalisé par N’jie ZAMON — NJIEZM.FR, développeur web, Le Robert (Martinique). Site : <a href="https://www.njiezm.fr" rel="noopener">www.njiezm.fr</a>. [À compléter : SIRET et adresse de contact avant mise en ligne publique.]</p>

        <h2>Hébergement</h2>
        <p>[À compléter : hébergeur du site lors de la mise en ligne.] Les vidéos ne sont pas hébergées par GADÉ : elles sont diffusées par YouTube (Google Ireland Limited, Gordon House, Barrow Street, Dublin 4, Irlande).</p>

        <h2>Contenus et droits</h2>
        <p>Les vidéos, titres originaux et images de prévisualisation appartiennent à leurs ayants droit respectifs (chaînes officielles indiquées sur chaque fiche). GADÉ se contente d’orienter vers ces vidéos avec le lecteur intégré officiel de YouTube, sans téléchargement ni copie. Les descriptions ont été rédigées pour GADÉ. Le logo et l’identité visuelle GADÉ sont des créations NJIEZM.FR.</p>

        <h2>Confidentialité</h2>
        <ul>
            <li>Accès réservé à la famille : un compte (e-mail et mot de passe haché), des profils (prénom, avatar, code PIN facultatif haché) et, pour chaque profil, « Ma liste », les positions de lecture et l’historique « Déjà vu ». Ces données restent sur le serveur de GADÉ, ne sont ni revendues ni partagées, et disparaissent avec le profil.</li>
            <li>Cookies : uniquement le cookie de session (connexion et profil choisi), le jeton anti-falsification et, si vous cochez « Rester connecté », un cookie de connexion. Aucun cookie publicitaire ni de mesure d’audience.</li>
            <li>Le lecteur utilise le domaine youtube-nocookie.com (mode confidentialité renforcée) : YouTube ne dépose ses cookies qu’au lancement de la lecture. Les règles de confidentialité de Google s’appliquent alors à la vidéo.</li>
        </ul>
    </div>
@endsection
