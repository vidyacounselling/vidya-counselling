<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        \Illuminate\Pagination\Paginator::defaultView('components.pagination');
        RateLimiter::for('comments', fn (Request $r) => Limit::perMinute(3)->by('comment:'.$r->ip()));
        RateLimiter::for('uploads', fn (Request $r) => Limit::perMinute(20)->by('upload:'.$r->user()?->id));
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            Limit::perMinute(5)->by('login-email:'.hash('sha256', mb_strtolower((string) $request->input('email')))),
        ]);
        RateLimiter::for('setup', fn (Request $request) => Limit::perMinute(5)->by('setup:'.$request->ip()));
    }
}
