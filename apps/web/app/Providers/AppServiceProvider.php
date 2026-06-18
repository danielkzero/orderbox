<?php

namespace App\Providers;

use App\Support\CompanyContext;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(CompanyContext::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('api-auth', fn (Request $request) => [
            Limit::perMinute(10)->by('ip:'.$request->ip()),
            Limit::perMinute(5)->by('client:'.($request->header('X-OrderBox-Client-Key') ?: $request->ip())),
        ]);

        RateLimiter::for('sensitive-write', fn (Request $request) => Limit::perMinute(30)
            ->by('user:'.($request->user()?->id ?: $request->ip())));

        RateLimiter::for('export', fn (Request $request) => Limit::perMinute(5)
            ->by('user:'.($request->user()?->id ?: $request->ip())));
    }
}
