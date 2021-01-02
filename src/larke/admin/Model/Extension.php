<?php

declare (strict_types = 1);

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
        'keywordlist',
        'authorlist',
        'configs',
        'config_datas',
        'requires',
    ];
    
    public $incrementing = false;
    public $timestamps = false;
    
    public function getKeywordlistAttribute() 
    {
        $value = $this->keywords;
        if (empty($value)) {
            return [];
        }
        
        return json_decode($value, true);
    }
    
    public function getAuthorlistAttribute() 
    {
        $value = $this->authors;
        if (empty($value)) {
            return [];
        }
        
        return json_decode($value, true);
    }
    
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
    
    public function getRequiresAttribute() 
    {
        $value = $this->require;
        if (empty($value)) {
            return [];
        }
        
        return json_decode($value, true);
    }
    
    public static function has(string $name)
    {
        return static::where('name', $name)
            ->exists();
    }
    
    public static function versionSatisfies(string $name, string $constraints = null)
    {
        $version = static::where('name', $name)
            ->first()
            ->version;
        
        try {
            $versionCheck =  Semver::satisfies($version, $constraints);
        } catch(\Exception $e) {
            return false;
        }
        
        return $versionCheck;
    }
    
    public static function getExtensions()
    {
        return Cache::rememberForever(md5('larkeadmin.model.extensions'), function() {
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
        Cache::forget(md5('larkeadmin.model.extensions'));
    }
    
    /**
     * 检测扩展依赖
     * 
     * @param string $name
     * @return array|null
     */
    public static function checkRequireExtension(array $requireExtensions = [])
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
                try {
                    $versionCheck = Semver::satisfies($installExtensions[$name], $version);
                } catch(\Exception $e) {
                    $versionCheck = false;
                }
                
                if ($versionCheck) {
                    $requireExtensionData = [
                        'name' => $name,
                        'version' => $version,
                        'install_version' => $installExtensions[$name],
                        'match' => true,
                    ];
                } else {
                    $requireExtensionData = [
                        'name' => $name,
                        'version' => $version,
                        'install_version' => $installExtensions[$name],
                        'match' => false,
                    ];
                }
            } else {
                $requireExtensionData = [
                    'name' => $name,
                    'version' => $version,
                    'install_version' => '',
                    'match' => false,
                ];
            }
            
            $data[] = $requireExtensionData;
        }
        
        return $data;
    }
    
}