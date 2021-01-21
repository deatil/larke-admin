<?php

declare (strict_types = 1);

namespace Larke\Admin\Model;

use Composer\Semver\Semver;

use Illuminate\Support\Facades\Cache;

/*
 * 扩展
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
    
    /**
     * 版本检测
     *
     * @return void
     */
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
    
    /**
     * 缓存扩展
     *
     * @return void
     */
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
    
    /**
     * 清空缓存
     *
     * @return void
     */
    public function clearCahce()
    {
        Cache::forget(md5('larkeadmin.model.extensions'));
    }
    
    /**
     * 检测是否安装
     *
     * @return void
     */
    public static function has(string $name)
    {
        return static::where('name', $name)
            ->exists();
    }

    /**
     * 判断是否启用
     *
     * @return bool
     */
    public static function enabled($name)
    {
        return static::where('name', $name)
            ->exists();
    }

    /**
     * 判断是否禁用
     *
     * @return bool
     */
    public static function disabled($name)
    {
        return ! $this->enabled($name);
    }
    
    /**
     * 检测扩展依赖
     * 
     * @param array $requireExtensions
     * @return array
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