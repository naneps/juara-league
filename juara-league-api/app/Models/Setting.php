<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type'
    ];

    /**
     * Get setting value by key with optional default
     */
    public static function get(string $key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        if (!$setting) return $default;

        return self::castValue($setting->value, $setting->type);
    }

    /**
     * Set setting value by key
     */
    public static function set(string $key, $value, string $group = 'general', string $type = 'string')
    {
        $val = is_array($value) || is_object($value) ? json_encode($value) : $value;
        
        return self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $val,
                'group' => $group,
                'type' => $type
            ]
        );
    }

    /**
     * Cast value based on type
     */
    protected static function castValue($value, string $type)
    {
        if ($value === null) return null;

        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'number' => is_numeric($value) ? (float) $value : $value,
            'json' => json_decode($value, true),
            default => $value,
        };
    }
}
