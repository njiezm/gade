<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Pa ni rézo · GADÉ</title>
    <meta name="robots" content="noindex, nofollow">
    <meta name="theme-color" content="#160e0b">
    <meta name="color-scheme" content="dark">
    <link rel="icon" type="image/svg+xml" href="/images/brand/favicon.svg">
    <link rel="manifest" href="/manifest.webmanifest">
    @vite(['resources/scss/app.scss'])
</head>
<body class="page-hors-ligne">
    {{-- Page neutre, sans menu ni profil : elle est conservée par le service worker. --}}
    <main class="hors-ligne" id="contenu">
        <img src="/images/brand/logo-gade.svg" alt="GADÉ — Sinéma lakay" width="260" height="104" class="hors-ligne-logo">
        <p class="hand hors-ligne-creole" lang="gcf">Pa ni rézo…</p>
        <h1 class="hors-ligne-titre">Il faut Internet pour regarder</h1>
        <p class="hors-ligne-texte">Les films de GADÉ sont lus depuis YouTube : reconnectez le Wi-Fi ou les données mobiles, puis réessayez.</p>
        <a class="btn btn-gade btn-lg" href="/">Réessayer</a>
    </main>
</body>
</html>
