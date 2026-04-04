<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;

class SettingService
{
    private ?Collection $cache = null;

    public function get(string $key, $default = null)
    {
        return $this->all()->get($key, $default);
    }

    public function set(string $key, $value): void
    {
        $userId = $this->userId();

        if ($userId === null) {
            throw new \RuntimeException('Unable to save settings for unauthenticated user.');
        }

        Setting::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value],
        );

        $this->forgetCache();
    }

    public function getMany(array $keys): array
    {
        $settings = $this->all();

        return array_reduce($keys, function ($carry, $key) use ($settings) {
            $carry[$key] = $settings->get($key);

            return $carry;
        }, []);
    }

    public function forgetCache(): void
    {
        $this->cache = null;
    }

    public function all(): Collection
    {
        if ($this->cache !== null) {
            return $this->cache;
        }

        $userId = $this->userId();

        if ($userId === null) {
            return $this->cache = collect();
        }

        $this->cache = Setting::query()
            ->where('user_id', $userId)
            ->get()
            ->mapWithKeys(fn (Setting $setting) => [$setting->key => $setting->value]);

        return $this->cache;
    }

    private function userId(): ?int
    {
        return Auth::id();
    }
}
