@php
    $siteName = config('gade.name');
    $pageTitle = trim($__env->yieldContent('title'));
    $fullTitle = $pageTitle ? "{$pageTitle} · {$siteName}" : "{$siteName} — Sinéma lakay : films, séries et documentaires en français";
    $metaDescription = trim($__env->yieldContent('description')) ?: config('gade.description');
    $ogImage = trim($__env->yieldContent('og_image')) ?: asset('images/brand/og-gade.jpg');
    $canonical = url()->current();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>{{ $fullTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ $canonical }}">
    <meta name="theme-color" content="#160e0b">
    <meta name="color-scheme" content="dark">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    {{-- Accès famille : site privé, jamais indexé --}}
    <meta name="robots" content="noindex, nofollow">
    @isset($profilActif)
        <meta name="gade-profil" content="{{ $profilActif->id }}" data-prenom="{{ $profilActif->prenom }}" data-import="{{ route('moi.importer') }}" data-liste="{{ url('/moi/liste') }}" data-progression="{{ route('moi.progression') }}">
    @endisset

    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_FR">
    <meta property="og:site_name" content="{{ $siteName }} — Sinéma lakay">
    <meta property="og:title" content="{{ $fullTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta name="twitter:card" content="summary_large_image">

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="32x32">
    <link rel="icon" type="image/svg+xml" href="{{ asset('images/brand/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/pwa/apple-touch-icon-180.png') }}">
    {{-- PWA : installable (manifest + service worker /sw.js, voir resources/js/pwa.js) --}}
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="GADÉ">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <link rel="preconnect" href="https://i.ytimg.com">

    @stack('head')

    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="@yield('body_class')">
    <a href="#contenu" class="skip-link">Aller au contenu</a>

    @include('partials.header')

    <main id="contenu">
        @if (session('status'))
            <div class="flash" role="status" data-flash>{{ session('status') }}</div>
        @endif
        @if ($errors->has('profil'))
            <div class="flash erreur" role="alert" data-flash>{{ $errors->first('profil') }}</div>
        @endif

        @yield('content')
    </main>

    @include('partials.footer')
</body>
</html>
