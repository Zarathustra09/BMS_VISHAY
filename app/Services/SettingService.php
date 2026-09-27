<?php

namespace App\Services;

use App\Interfaces\SettingInterface;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingService implements SettingInterface
{
    private const CACHE_KEY = 'app.settings';

    public function all(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $stored = Setting::query()
                ->pluck('value', 'key')
                ->map(fn ($value) => json_decode($value, true))
                ->toArray();

            return array_merge(self::DEFAULTS, $stored);
        });
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    public function update(array $values): void
    {
        foreach ($values as $key => $value) {
            if (!in_array($key, self::KEYS, true)) {
                continue;
            }

            Setting::updateOrCreate(['key' => $key], ['value' => json_encode($value)]);
        }

        Cache::forget(self::CACHE_KEY);
    }
}
