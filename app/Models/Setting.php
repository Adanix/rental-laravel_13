<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("setting.{$key}", function () use ($key, $default) {
            $record = static::find($key);
            if (! $record) {
                return $default;
            }

            $val = $record->value;
            // Check if JSON
            if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $decoded;
                }
            }

            return $val;
        });
    }

    public static function set(string $key, mixed $value): void
    {
        $serialized = is_array($value) ? json_encode($value) : (string) $value;

        static::updateOrCreate(
            ['key' => $key],
            ['value' => $serialized]
        );

        Cache::forget("setting.{$key}");
    }

    public static function allKeyed(): array
    {
        return static::all()->mapWithKeys(function ($item) {
            $val = $item->value;
            if (is_string($val) && (str_starts_with($val, '{') || str_starts_with($val, '['))) {
                $decoded = json_decode($val, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return [$item->key => $decoded];
                }
            }
            return [$item->key => $val];
        })->toArray();
    }
}
