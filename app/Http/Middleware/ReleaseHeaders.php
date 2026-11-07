<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class ReleaseHeaders {
 public function handle(Request $request,Closure $next) { $response=$next($request);$path=base_path('bootstrap/release.json');if(is_file($path)){$manifest=json_decode(file_get_contents($path),true);if(preg_match('/^[a-f0-9]{40}$/',$manifest['commit']??''))$response->headers->set('X-Vidya-Release',$manifest['commit']);}$response->headers->set('X-Content-Type-Options','nosniff');$response->headers->set('Referrer-Policy','strict-origin-when-cross-origin');$response->headers->set('X-Frame-Options','SAMEORIGIN');if($request->is('admin*')||$request->routeIs('home','contact','reviews','service','services','our-story','client-stories','privacy','blog.*'))$response->headers->set('Cache-Control','private, no-store');return $response; }
}
