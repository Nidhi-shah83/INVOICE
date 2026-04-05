<?php

use App\Services\SettingService;

if (! function_exists('setting')) {
    function setting(string $key, $default = null)
    {
        $hasExplicitDefault = func_num_args() >= 2;

        try {
            $service = app(SettingService::class);

            return $hasExplicitDefault
                ? $service->get($key, $default)
                : $service->get($key);
        } catch (\Throwable $exception) {
            if ($hasExplicitDefault) {
                return $default;
            }

            return SettingService::defaultFor($key);
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
            return ['code' => 'INR', 'symbol' => '₹'];
        }

        return [
            'code' => $locs[$country]['currency'] ?? 'INR',
            'symbol' => $locs[$country]['symbol'] ?? '₹',
        ];
    }
}

if (! function_exists('update_dotenv')) {
    function update_dotenv(array $values): bool
    {
        $path = base_path('.env');

        if (! file_exists($path) || ! is_writable($path)) {
            return false;
        }

        $content = file_get_contents($path);

        foreach ($values as $key => $value) {
            $pattern = '/^'.preg_quote($key, '/').'=.*$/m';
            $replacement = $key.'='.format_dotenv_value($value);

            if (preg_match($pattern, $content)) {
                $content = preg_replace($pattern, $replacement, $content);
            } else {
                $content .= PHP_EOL.$replacement;
            }
        }

        return file_put_contents($path, $content) !== false;
    }
}

if (! function_exists('format_dotenv_value')) {
    function format_dotenv_value($value): string
    {
        if ($value === null) {
            return 'null';
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        $value = (string) $value;

        if (strtolower(trim($value)) === 'null') {
            return 'null';
        }

        $escaped = str_replace('"', '\\"', $value);

        return '"'.$escaped.'"';
    }
}
