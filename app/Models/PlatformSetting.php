<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
     protected $fillable = ['key', 'value', 'display_name', 'group', 'description'];

    public static function updateSetting($key, $value)
    {
        return self::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );
    }

      public static function getValue(string $key, $default = null)
    {
        return cache()->remember("platform_setting_{$key}", 3600, function () use ($key, $default) {
            return self::where('key', $key)->value('value') ?? $default;
        });
    }

 protected static function booted()
    {
        static::updated(function ($setting) {
            cache()->forget("platform_setting_{$setting->key}");
            cache()->forget('platform_settings_all');
        });

        static::deleted(function ($setting) {
            cache()->forget("platform_setting_{$setting->key}");
            cache()->forget('platform_settings_all');
        });
    }
}

