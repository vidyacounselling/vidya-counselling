<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class ReleaseHeaders {
 public function handle(Request $request,Closure $next) { $response=$next($request);$path=base_path('bootstrap/release.json');if(is_file($path)){$manifest=json_decode(file_get_contents($path),true);if(preg_match('/^[a-f0-9]{40}$/',$manifest['commit']??''))$response->headers->set('X-Vidya-Release',$manifest['commit']);}$response->headers->set('X-Content-Type-Options','nosniff');return $response; }
}
