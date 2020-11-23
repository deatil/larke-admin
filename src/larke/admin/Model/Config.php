<?php

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
    
    public static function getSettings()
    {
        return Cache::rememberForever(md5('larkeadmin.model.config.settings'), function() {
            return self::all()->mapWithKeys(function ($setting) {
                return [$setting->key => $setting->value];
            });
        });
    }
    
    public static function clearCahce()
    {
        Cache::forget(md5('larkeadmin.model.config.settings'));
    }
    
    public static function has($key)
    {
        return static::where('name', $key)->exists();
    }
    
    public static function get($key, $default = null)
    {
        return static::where('name', $key)->first()->value ?? $default;
    }
    
    public static function set($key, $value)
    {
        return static::updataOrCreate(['name' => $key], ['value' => $value]);
    }
    
    public static function setMany($settings)
    {
        foreach ($settings as $key => $value) {
            return static::set($key, $value);
        }
    }
    
    public static function remove($key)
    {
        $deleted = static::where('name', $key)->first()->delete();
        Cache::forget(md5('larkeadmin.model.config.settings'));
        return $deleted;
    }
    
}