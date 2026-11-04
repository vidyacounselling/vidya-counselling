<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ProtectStaging
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('vidya.staging_protection')) {
            $hash = config('vidya.staging_password_hash');
            if (! $hash || ! hash_equals((string) config('vidya.staging_username'), (string) $request->getUser())
                || ! Hash::check((string) $request->getPassword(), $hash)) {
                return response('Staging authentication required.', 401, [
                    'WWW-Authenticate' => 'Basic realm="Vidya staging", charset="UTF-8"',
                    'X-Robots-Tag' => 'noindex, nofollow, noarchive',
                    'Cache-Control' => 'no-store',
                ]);
            }
        }
        $response = $next($request);
        if (config('vidya.staging_protection') || ! $request->is('/')) {
            $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
            $response->headers->set('Cache-Control', 'no-store, private');
        }
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        return $response;
    }
}
