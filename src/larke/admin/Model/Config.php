<?php

declare (strict_types = 1);

namespace Larke\Admin\Model;

use Illuminate\Support\Facades\Cache;

/*
 * Config
 *
 * @create 2020-10-24
 * @author deatil
 */
class Config extends Base
{
    protected $table = 'larke_config';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    public $incrementing = false;
    public $timestamps = false;
    
    public static function getSettings(): array
    {
        return Cache::rememberForever(md5('larkeadmin.model.config.settings'), function() {
            return self::where('status', '=', 1)
                ->get()
                ->mapWithKeys(function ($setting) {
                    return [$setting->name => $setting->value];
                })
                ->sort()
                ->toArray();
        });
    }
    
    public static function clearCahce(): void
    {
        Cache::forget(md5('larkeadmin.model.config.settings'));
    }
    
    public static function has(string $key): bool
    {
        return static::where('name', $key)
            ->exists();
    }
    
    public static function get(string $key, ?string $default = null): string
    {
        return static::where('name', $key)
            ->first()
            ->value ?? $default;
    }
    
    public static function set(string $key, ?string $value): bool
    {
        return static::where('name', '=', $key)
            ->first()
            ->update([
                'value' => $value
            ]);
    }
    
    public static function setMany(array $settings = []): void
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }
    
    public static function remove(string $key): bool
    {
        $deleted = static::where('name', $key)->first()->delete();
        Cache::forget(md5('larkeadmin.model.config.settings'));
        return $deleted;
    }
    
}