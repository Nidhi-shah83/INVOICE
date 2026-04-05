<?php

namespace App\Providers;

use App\Services\SettingService;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
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
        View::composer('*', function ($view) {
            $view->with('settingsService', app(SettingService::class));
        });

        $settingsService = app(SettingService::class);

        Config::set('mail.default', $settingsService->get('mail_mailer', config('mail.default')));
        Config::set('mail.mailers.smtp.scheme', $settingsService->get('mail_scheme', config('mail.mailers.smtp.scheme')));
        Config::set('mail.mailers.smtp.host', $settingsService->get('mail_host', config('mail.mailers.smtp.host')));
        Config::set('mail.mailers.smtp.port', $settingsService->get('mail_port', config('mail.mailers.smtp.port')));
        Config::set('mail.mailers.smtp.username', $settingsService->get('mail_username', config('mail.mailers.smtp.username')));
        Config::set('mail.mailers.smtp.password', $settingsService->get('mail_password', config('mail.mailers.smtp.password')));
        Config::set('mail.from.address', $settingsService->get('mail_from_address', config('mail.from.address')));
        Config::set('mail.from.name', $settingsService->get('mail_from_name', config('mail.from.name')));

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
