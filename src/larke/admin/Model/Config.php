<?php

declare (strict_types = 1);

namespace Larke\Admin\Model;

use Illuminate\Support\Facades\Cache;

/*
 * 配置
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
    
    public static function getSettings()
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
    
    public static function clearCahce()
    {
        Cache::forget(md5('larkeadmin.model.config.settings'));
    }
    
    public static function has(string $key)
    {
        return static::where('name', $key)
            ->exists();
    }
    
    public static function get(string $key, $default = null)
    {
        return static::where('name', $key)
            ->first()
            ->value ?? $default;
    }
    
    public static function set(string $key, $value)
    {
        return static::where('name', '=', $key)
            ->first()
            ->update([
                'value' => $value
            ]);
    }
    
    public static function setMany(array $settings = [])
    {
        foreach ($settings as $key => $value) {
            static::set($key, $value);
        }
    }
    
    public static function remove(string $key)
    {
        $deleted = static::where('name', $key)->first()->delete();
        Cache::forget(md5('larkeadmin.model.config.settings'));
        return $deleted;
    }
    
}