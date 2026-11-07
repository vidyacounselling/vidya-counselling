<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo('/login');
        $middleware->redirectUsersTo('/admin');
        $middleware->alias(['admin' => \App\Http\Middleware\RequireAdmin::class, 'staff' => \App\Http\Middleware\RequireStaff::class]);
        $middleware->validateCsrfTokens(except: ['register']);
        $middleware->web(append: [\App\Http\Middleware\ProtectStaging::class,\App\Http\Middleware\ReleaseHeaders::class]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'setup_token', 'full_name', 'email', 'mobile', 'message', 'preferred_window', 'note', 'private_notes', 'moderation_notes']);
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
