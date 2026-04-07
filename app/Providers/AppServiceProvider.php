<?php

namespace App\Providers;

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
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('n8n-user', function (Request $request): Limit {
            $userId = (string) ($request->user()?->id ?? $request->input('user_id') ?? $request->ip());

            return Limit::perMinute(10)
                ->by('n8n-user:'.$userId)
                ->response(function (Request $request, array $headers) {
                    return response()->json([
                        'message' => 'Rate limit exceeded. Please retry in a minute.',
                    ], 429, $headers);
                });
        });
    }
}
