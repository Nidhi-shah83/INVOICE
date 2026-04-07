<?php

use App\Models\Setting;
use App\Services\SettingService;
use Illuminate\Support\Facades\Crypt;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        if (! auth()->check()) {
            return func_num_args() >= 2 ? $default : SettingService::defaultFor($key);
        }

        $value = Setting::query()
            ->where('key', $key)
            ->value('value');

        if ($value === null) {
            return func_num_args() >= 2 ? $default : SettingService::defaultFor($key);
        }

        return decode_setting_value($key, $value);
    }
}

if (! function_exists('setting_for_user')) {
    function setting_for_user(int $userId, string $key, $default = null)
    {
        $value = Setting::query()
            ->withoutGlobalScopes()
            ->where('user_id', $userId)
            ->where('key', $key)
            ->value('value');

        if ($value === null) {
            return $default;
        }

        return decode_setting_value($key, $value);
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
            'mail.default' => setting_for_user($resolvedUserId, 'mail_mailer', config('mail.default')),
            'mail.mailers.smtp.scheme' => setting_for_user($resolvedUserId, 'mail_scheme', config('mail.mailers.smtp.scheme')),
            'mail.mailers.smtp.host' => setting_for_user($resolvedUserId, 'mail_host', config('mail.mailers.smtp.host')),
            'mail.mailers.smtp.port' => setting_for_user($resolvedUserId, 'mail_port', config('mail.mailers.smtp.port')),
            'mail.mailers.smtp.username' => setting_for_user($resolvedUserId, 'mail_username', config('mail.mailers.smtp.username')),
            'mail.mailers.smtp.password' => setting_for_user($resolvedUserId, 'mail_password', config('mail.mailers.smtp.password')),
            'mail.from.address' => setting_for_user($resolvedUserId, 'mail_from_address', config('mail.from.address')),
            'mail.from.name' => setting_for_user($resolvedUserId, 'mail_from_name', config('mail.from.name')),
        ]);
    }
}

if (! function_exists('decode_setting_value')) {
    function decode_setting_value(string $key, $value)
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $value = $decoded;
            }
        }

        if ($key !== 'mail_password' || ! is_string($value) || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString($value);
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
    function locations()
    {
        return config('locations.countries', []);
    }
}

if (! function_exists('get_states')) {
    function get_states(string $country = null): array
    {
        $country = $country ?: setting('country', 'India');
        $locs = locations();

        if (! isset($locs[$country])) {
            return [];
        }

        return $locs[$country]['states'] ?? [];
    }
}

if (! function_exists('get_currency')) {
    function get_currency(string $country = null): array
    {
        $country = $country ?: setting('country', 'India');
        $locs = locations();

        if (! isset($locs[$country])) {
            return ['code' => 'INR', 'symbol' => 'Rs'];
        }

        return [
            'code' => $locs[$country]['currency'] ?? 'INR',
            'symbol' => $locs[$country]['symbol'] ?? 'Rs',
        ];
    }
}
