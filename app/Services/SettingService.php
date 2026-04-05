<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class SettingService
{
    private array $cache = [];

    private bool $loaded = false;

    private const DEFAULTS = [
        'business_name' => 'Invoice Pro',
        'gstin' => '-',
        'state' => '-',
        'due_days' => 15,
        'default_due_days' => 15,
        'ai_call_enabled' => false,
    ];

    private const ALIASES = [
        'ai_call_enabled' => 'enable_ai_calls',
    ];

    public static function defaultFor(string $key)
    {
        return self::DEFAULTS[$key] ?? null;
    }

    public function get(string $key, $default = null)
    {
        $this->load();

        if (array_key_exists($key, $this->cache)) {
            return $this->cache[$key];
        }

        $aliasedKey = self::ALIASES[$key] ?? null;
        if ($aliasedKey && array_key_exists($aliasedKey, $this->cache)) {
            return $this->cache[$aliasedKey];
        }

        if ($key === 'due_days' && array_key_exists('default_due_days', $this->cache)) {
            return $this->cache['default_due_days'];
        }

        if ($key === 'default_due_days' && array_key_exists('due_days', $this->cache)) {
            return $this->cache['due_days'];
        }

        if (func_num_args() >= 2) {
            return $default;
        }

        return self::defaultFor($key);
    }

    public function set(string $key, $value): void
    {
        $writeKey = self::ALIASES[$key] ?? $key;
        $userId = $this->userId();

        if ($userId === null) {
            throw new \RuntimeException('Unable to save settings for unauthenticated user.');
        }

        if ($writeKey === 'mail_password' && $value !== null) {
            $value = encrypt((string) $value);
        }

        Setting::updateOrCreate(
            ['user_id' => $userId, 'key' => $writeKey],
            ['value' => $value],
        );

        // Keep request-level cache in sync to avoid unnecessary reloads.
        $this->cache[$writeKey] = $value;

        foreach (self::ALIASES as $alias => $canonicalKey) {
            if ($canonicalKey === $writeKey) {
                $this->cache[$alias] = $value;
            }
        }

        if (in_array($writeKey, ['due_days', 'default_due_days'], true)) {
            $pairedKey = $writeKey === 'due_days' ? 'default_due_days' : 'due_days';
            $this->cache[$pairedKey] = $value;
        }
    }

    public function getMany(array $keys): array
    {
        $this->load();

        return array_reduce($keys, function ($carry, $key) {
            $key = (string) $key;
            $carry[$key] = $this->get($key);

            return $carry;
        }, []);
    }

    public function forgetCache(): void
    {
        $this->cache = [];
        $this->loaded = false;
    }

    public function all(): Collection
    {
        $this->load();

        return collect($this->cache);
    }

    private function userId(): ?int
    {
        return Auth::id();
    }

    private function load(): void
    {
        if ($this->loaded) {
            return;
        }

        $this->loaded = true;

        $userId = $this->userId();

        if ($userId === null) {
            return;
        }

        $this->cache = Setting::query()
            ->where('user_id', $userId)
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value])
            ->all();

        if (isset($this->cache['mail_password'])) {
            $this->cache['mail_password'] = $this->decryptValue($this->cache['mail_password']);
        }
    }

    private function decryptValue($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        try {
            return Crypt::decryptString((string) $value);
        } catch (Throwable) {
            return $value;
        }
    }
}
