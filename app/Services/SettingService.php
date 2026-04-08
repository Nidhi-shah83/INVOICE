<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;
use Throwable;

class SettingService
{
    private const CACHE_TTL_SECONDS = 3600;

    private const DEFAULTS = [
        'business_name' => 'Invoice Pro',
        'logo' => null,
        'favicon' => null,
        'gstin' => '-',
        'address' => '',
        'country' => 'India',
        'state' => '',
        'currency' => 'INR',
        'currency_symbol' => 'Rs',
        'company_prefix' => null,
        'quote_prefix' => 'QT',
        'order_prefix' => 'ORD',
        'invoice_prefix' => 'INV',
        'default_due_days' => 15,
        'default_gst_rate' => 18,
        'email' => '',
        'phone' => '',
        'terms_conditions' => '',
        'mail_mailer' => 'smtp',
        'mail_port' => 587,
        'mail_from_address' => null,
        'mail_from_name' => null,
    ];

    public static function defaultFor(string $key)
    {
        return self::DEFAULTS[$key] ?? null;
    }

    public function get(string $key, $default = null)
    {
        $settings = $this->all();

        if (array_key_exists($key, $settings)) {
            return $settings[$key];
        }

        if (func_num_args() >= 2) {
            return $default;
        }

        return self::defaultFor($key);
    }

    public function set(string $key, $value): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            throw new RuntimeException('Unable to save settings for unauthenticated user.');
        }

        if ($key === 'mail_password' && $value !== null && $value !== '') {
            $value = Crypt::encryptString((string) $value);
        }

        Setting::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value],
        );

        Cache::forget($this->cacheKey($userId));
    }

    public function getMany(array $keys): array
    {
        $settings = $this->all();

        return array_reduce($keys, function (array $carry, $key) use ($settings): array {
            $key = (string) $key;
            $carry[$key] = array_key_exists($key, $settings)
                ? $settings[$key]
                : self::defaultFor($key);

            return $carry;
        }, []);
    }

    public function forgetCache(): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            return;
        }

        Cache::forget($this->cacheKey($userId));
    }

    public function all(): array
    {
        $userId = $this->userId();
        if ($userId === null) {
            return [];
        }

        return Cache::remember(
            "settings_user_".$userId,
            self::CACHE_TTL_SECONDS,
            fn () => $this->loadForUser($userId),
        );
    }

    private function userId(): ?int
    {
        $id = auth()->id();

        return is_int($id) ? $id : null;
    }

    private function cacheKey(int $userId): string
    {
        return "settings_user_{$userId}";
    }

    private function loadForUser(int $userId): array
    {
        $settings = Setting::query()
            ->withoutGlobalScopes()
            ->where('user_id', $userId)
            ->get(['key', 'value'])
            ->mapWithKeys(function (Setting $setting): array {
                return [$setting->key => $this->decodeValue($setting->key, $setting->value)];
            })
            ->all();

        if (array_key_exists('default_due_days', $settings) && ! array_key_exists('due_days', $settings)) {
            $settings['due_days'] = $settings['default_due_days'];
        }

        if (array_key_exists('due_days', $settings) && ! array_key_exists('default_due_days', $settings)) {
            $settings['default_due_days'] = $settings['due_days'];
        }

        return $settings;
    }

    private function decodeValue(string $key, $value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        if ($key !== 'mail_password') {
            return $value;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (Throwable) {
            return $value;
        }
    }
}
