<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSetting extends Model
{
    protected $table = 'app_settings';

    protected $fillable = [
        'key',
        'value',
        'description',
    ];

    /**
     * Helper to get setting value by key with fallback
     */
    public static function get(string $key, ?string $default = null): ?string
    {
        $setting = static::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }

    /**
     * Helper to set setting value by key
     */
    public static function set(string $key, ?string $value, ?string $description = null): static
    {
        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'description' => $description ?? ($key === 'class_change_code' ? 'Kode otorisasi untuk siswa berpindah tingkat kelas SD' : null)
            ]
        );
    }
}
