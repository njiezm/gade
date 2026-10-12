<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * En-têtes de sécurité, dont une CSP qui n'autorise que le lecteur YouTube intégré
 * (www.youtube-nocookie.com / www.youtube.com) et les vignettes i.ytimg.com.
 */
class EnTetesSecurite
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $csp = implode('; ', [
            "default-src 'self'",
            "script-src 'self' https://www.youtube.com https://s.ytimg.com",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https://i.ytimg.com",
            "font-src 'self'",
            "connect-src 'self'",
            'frame-src https://www.youtube-nocookie.com https://www.youtube.com',
            "media-src 'self'",
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "frame-ancestors 'self'",
        ]);

        $response->headers->set('Content-Security-Policy', $csp);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), fullscreen=(self "https://www.youtube-nocookie.com" "https://www.youtube.com")');
        // Accès famille : rien n'est indexé
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');
        // Pages connectées : jamais en cache partagé, ni dans l'historique d'un autre profil
        if ($request->user()) {
            $response->headers->set('Cache-Control', 'private, no-store, max-age=0');
        }

        return $response;
    }
}
