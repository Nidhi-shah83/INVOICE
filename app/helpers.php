<?php

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

if (! function_exists('setting')) {
    function setting($key, $default = null)
    {
        $service = app(SettingService::class);

        if (func_num_args() === 1) {
            return $service->get($key);
        }

        return $service->get($key, $default);
    }
}

if (! function_exists('setting_for_user')) {
    function setting_for_user(int $userId, string $key, $default = null)
    {
        $settings = Cache::remember("settings_user_{$userId}", 3600, function () use ($userId): array {
            return Setting::query()
                ->withoutGlobalScopes()
                ->where('user_id', $userId)
                ->get(['key', 'value'])
                ->mapWithKeys(function (Setting $setting): array {
                    return [$setting->key => decode_setting_value($setting->key, $setting->value)];
                })
                ->all();
        });

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        return $default;
    }
}

if (! function_exists('apply_user_mail_config')) {
    function apply_user_mail_config(?int $userId = null): void
    {
        $resolvedUserId = $userId ?: auth()->id();
        if (! $resolvedUserId) {
            return;
        }

        config([
            'mail.default' => setting_for_user($resolvedUserId, 'mail_mailer', 'smtp'),
            'mail.mailers.smtp.host' => setting_for_user($resolvedUserId, 'mail_host'),
            'mail.mailers.smtp.port' => setting_for_user($resolvedUserId, 'mail_port'),
            'mail.mailers.smtp.username' => setting_for_user($resolvedUserId, 'mail_username'),
            'mail.mailers.smtp.password' => setting_for_user($resolvedUserId, 'mail_password'),
            'mail.from.address' => setting_for_user($resolvedUserId, 'mail_from_address'),
            'mail.from.name' => setting_for_user($resolvedUserId, 'mail_from_name'),
        ]);

        if (app()->bound('mail.manager')) {
            app('mail.manager')->forgetMailers();
        }
    }
}

if (! function_exists('decode_setting_value')) {
    function decode_setting_value(string $key, $value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($key !== 'mail_password') {
            return $value;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (\Throwable) {
            return $value;
        }
    }
}

if (! function_exists('setting_bool')) {
    function setting_bool(string $key, bool $default = false): bool
    {
        $value = setting($key, $default);

        if (is_bool($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            return in_array(strtolower(trim($value)), ['1', 'true', 'yes', 'on', 'enabled'], true);
        }

        return (bool) $value;
    }
}

if (! function_exists('locations')) {
    function locations(): array
    {
        return config('locations.countries', []);
    }
}

if (! function_exists('get_states')) {
    function get_states(string $country = null): array
    {
        $country = $country ?: setting('country', 'India');
        $locations = locations();

        if (! isset($locations[$country])) {
            return [];
        }

        return $locations[$country]['states'] ?? [];
    }
}

if (! function_exists('get_currency')) {
    function get_currency(string $country = null): array
    {
        $country = $country ?: setting('country', 'India');
        $locations = locations();

        if (! isset($locations[$country])) {
            return [
                'code' => setting('currency', 'INR'),
                'symbol' => setting('currency_symbol', 'Rs'),
            ];
        }

        return [
            'code' => $locations[$country]['currency'] ?? 'INR',
            'symbol' => $locations[$country]['symbol'] ?? 'Rs',
        ];
    }
}

if (! function_exists('normalize_storage_path')) {
    function normalize_storage_path(?string $path): ?string
    {
        if (! is_string($path)) {
            return null;
        }

        $normalized = trim($path);
        if ($normalized === '') {
            return null;
        }

        return ltrim((string) preg_replace('#^/?storage/#', '', $normalized), '/');
    }
}

if (! function_exists('storage_public_url')) {
    function storage_public_url(?string $path): ?string
    {
        $normalized = normalize_storage_path($path);
        if (! $normalized) {
            return null;
        }

        if (! Storage::disk('public')->exists($normalized)) {
            return null;
        }

        return Storage::url($normalized);
    }
}

if (! function_exists('setting_media_url')) {
    function setting_media_url(string $primaryKey, ?string $fallbackKey = null): ?string
    {
        $primary = setting($primaryKey);

        if (is_string($primary) && $primary !== '') {
            return storage_public_url($primary);
        }

        if ($fallbackKey !== null) {
            $fallback = setting($fallbackKey);
            if (is_string($fallback) && $fallback !== '') {
                return storage_public_url($fallback);
            }
        }

        return null;
    }
}
