<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = ['setting_key', 'setting_value', 'updated_by'];

    /**
     * Get a setting value by key, with optional default.
     * Cached for 60 seconds to avoid repeated DB hits.
     */
    public static function get(string $key, $default = null)
    {
        return Cache::remember("setting.{$key}", 60, function () use ($key, $default) {
            $setting = static::where('setting_key', $key)->first();
            return $setting ? $setting->setting_value : $default;
        });
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, $userId = null): void
    {
        static::updateOrCreate(
            ['setting_key' => $key],
            ['setting_value' => $value, 'updated_by' => $userId ?? auth()->id()]
        );

        Cache::forget("setting.{$key}");
    }

    /**
     * Get all settings as key => value array.
     */
    public static function allSettings(): array
    {
        return Cache::remember('all_settings', 60, function () {
            return static::pluck('setting_value', 'setting_key')->toArray();
        });
    }

    /**
     * Flush the entire settings cache.
     */
    public static function flushCache(): void
    {
        $keys = static::pluck('setting_key');
        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
        Cache::forget('all_settings');
    }
}
