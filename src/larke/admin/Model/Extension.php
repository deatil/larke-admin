<?php

namespace Larke\Admin\Model;

use Composer\Semver\Semver;

use Illuminate\Support\Facades\Cache;

/*
 * Extension
 *
 * @create 2020-10-30
 * @author deatil
 */
class Extension extends Base
{
    protected $table = 'larke_extension';
    protected $keyType = 'string';
    protected $primaryKey = 'id';
    
    protected $guarded = [];
    
    protected $appends = [
        'configs',
        'config_datas',
        'require_extensions',
    ];
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function getConfigsAttribute() 
    {
        $value = $this->config;
        if (empty($value)) {
            return [];
        }
        
        return json_decode($value, true);
    }
    
    public function getConfigDatasAttribute() 
    {
        $value = $this->config_data;
        if (empty($value)) {
            return [];
        }
        
        return json_decode($value, true);
    }
    
    public function getRequireExtensionsAttribute() 
    {
        $value = $this->require_extension;
        if (empty($value)) {
            return [];
        }
        
        return json_decode($value, true);
    }
    
    public static function getExtensions()
    {
        return Cache::rememberForever(md5('larke.model.extensions'), function() {
            return self::orderBy('listorder', 'ASC')
                ->orderBy('installtime', 'ASC')
                ->get()
                ->mapWithKeys(function ($extension) {
                    return [$extension->name => $extension->toArray()];
                })
                ->toArray();
        });
    }
    
    public function clearCahce()
    {
        Cache::forget(md5('larke.model.extensions'));
    }
    
    /**
     * 检测扩展依赖
     * 
     * @param string $name
     * @return array|null
     */
    public static function checkRequireExtension($requireExtensions = [])
    {
        if (empty($requireExtensions)) {
            return [];
        }
        
        $requireExtensionNames = collect($requireExtensions)
            ->filter(function($data) {
                return !empty($data);
            })
            ->map(function($data, $key) {
                return $key;
            });
        
        $installExtensions = self::whereIn('name', $requireExtensionNames)
            ->select(['name', 'version'])
            ->get()
            ->mapWithKeys(function ($extension) {
                return [
                    $extension->name => $extension->version,
                ];
            })
            ->toArray();
        
        $data = [];
        foreach ($requireExtensions as $name => $version) {
            if (isset($installExtensions[$name])) {
                $versionCheck = Semver::satisfies($installExtensions[$name], $version);
                if ($versionCheck) {
                    $requireExtensionData = [
                        'name' => $name,
                        'version' => $version,
                        'install_version' => $installExtensions[$name],
                        'match' => 1,
                    ];
                } else {
                    $requireExtensionData = [
                        'name' => $name,
                        'version' => $version,
                        'install_version' => $installExtensions[$name],
                        'match' => 0,
                    ];
                }
            } else {
                $requireExtensionData = [
                    'name' => $name,
                    'version' => $version,
                    'install_version' => '',
                    'match' => 0,
                ];
            }
            
            $data[] = $requireExtensionData;
        }
        
        return $data;
    }
    
}