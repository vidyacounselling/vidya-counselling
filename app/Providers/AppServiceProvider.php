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
        foreach([\App\Models\Post::class,\App\Models\Service::class,\App\Models\Category::class,\App\Models\Tag::class] as $model){$model::saved(fn($m)=>app(\App\Services\PageRegistry::class)->sync($m));$model::deleted(fn($m)=>app(\App\Services\PageRegistry::class)->sync($m));}
        \Illuminate\Support\Facades\View::composer('layouts.public',function($view){$registry=app(\App\Services\PageRegistry::class);$page=$registry->current();$settings=app(\App\Services\WebsiteSettings::class)->all();$view->with(['website'=>$settings,'brandLogo'=>\App\Models\Media::find($settings['logo_media_id']??null),'socialImage'=>\App\Models\Media::find($settings['default_social_image']??null),'currentPublicPage'=>$page,'assignedFaqs'=>$registry->faqs($page)]);});
        RateLimiter::for('inquiries',fn(Request $r)=>Limit::perMinute(5)->by('inquiries:'.$r->ip()));
        RateLimiter::for('reviews',fn(Request $r)=>Limit::perMinute(3)->by('reviews:'.$r->ip()));
        RateLimiter::for('comments', fn (Request $r) => Limit::perMinute(3)->by('comment:'.$r->ip()));
        RateLimiter::for('uploads', fn (Request $r) => Limit::perMinute(20)->by('upload:'.$r->user()?->id));
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(20)->by('login-ip:'.$request->ip()),
            Limit::perMinute(5)->by('login-email:'.hash('sha256', mb_strtolower((string) $request->input('email')))),
        ]);
        RateLimiter::for('setup', fn (Request $request) => Limit::perMinute(5)->by('setup:'.$request->ip()));
    }
}
