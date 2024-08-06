<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

use Larke\Admin\Model\Extension as ExtensionModel;

/*
 * 配置
 *
 * @create 2021-6-6
 * @author deatil
 */
class Config
{
    /**
     * 获取配置列表
     *
     * @param  string $name 扩展包名
     * @return \Illuminate\Support\Collection 配置列表
     */
    public static function name(string $name): Collection
    {
        $extensions = ExtensionModel::getExtensions();
        $config     = Arr::get($extensions, $name.'.config_datas', []);
        
        return collect($config);
    }
    
    /**
     * 获取扩展配置
     *
     * @param  string $name    扩展包名
     * @param  string $key     配置关键字
     * @param  mixed  $default 默认值
     * @return mixed 配置数据
     */
    public static function get(string $name, ?string $key = null, mixed $default = null): Collection
    {
        $data = static::name($name);
        if (empty($key)) {
            return $data;
        }
        
        return $data->get($key, $default);
    }
}
