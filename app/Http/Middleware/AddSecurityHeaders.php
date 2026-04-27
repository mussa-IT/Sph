<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AddSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Response $response */
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', (string) config('security.referrer_policy', 'strict-origin-when-cross-origin'));
        $response->headers->set('Permissions-Policy', (string) config('security.permissions_policy', 'camera=(), microphone=(), geolocation=()'));
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');

        if ($request->isSecure()) {
            $hstsMaxAge = (int) config('security.hsts_max_age', 31536000);
            $response->headers->set('Strict-Transport-Security', 'max-age=' . $hstsMaxAge . '; includeSubDomains');
        }

        return $response;
    }
}
