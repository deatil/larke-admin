<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Illuminate\Support\Arr;
use Illuminate\Support\Traits\Macroable;

/*
 * 扩展信息
 *
 * @create 2022-1-19
 * @author deatil
 */
class Info
{
    use Macroable;
    
    /**
     * 服务提供者名称
     */
    protected $name = '';
    
    /**
     * 扩展信息
     */
    protected $info = [
        // 扩展名称
        'title' => '',
        // 扩展描述
        'description' => '',
        // 扩展关键字
        'keywords' => [
            'larke',
            'extension',
        ],
        // 扩展主页
        'homepage' => 'http://github.com/deatil',
        // 作者
        'authors' => [
            [
                'name' => 'deatil', 
                'email' => 'deatil@github.com', 
                'homepage' => 'http://github.com/deatil', 
            ],
        ],
        // 版本号
        'version' => '1.0.2',
        // 适配系统版本
        'adaptation' => '1.1.*',
        // 依赖扩展[选填]
        'require' => [
            // 'larke/log-viewer' => '1.0.*'
        ], 
    ];
    
    /**
     * 扩展图标
     */
    protected $icon = '';
    
    /**
     * 扩展配置[选填]
     */
    protected $config = [];
    
    /**
     * 构造函数
     *
     * @param   string  $name   服务提供者名称
     * @param   array   $info   扩展信息
     * @param   string  $icon   扩展图标
     * @param   array   $config 扩展配置
     */
    public function __construct(
        string $name  = '',
        array $info   = [],
        string $icon  = '',
        array $config = []
    ) {
        $this->withName($name);
        $this->withInfo($info);
        $this->withIcon($icon);
        $this->withConfig($config);
    }
    
    /**
     * 使用
     *
     * @param   string|array $name   服务提供者名称
     * @param   array        $info   扩展信息
     * @param   string       $icon   扩展图标
     * @param   array        $config 扩展配置
     * @return  object       $this
     */
    public static function make(
        $name = null, 
        array $info = [], 
        string $icon = '', 
        array $config = []
    ) {
        if (is_array($name)) {
            $name = Arr::get($name, "name", "");
            $info = Arr::get($name, "info", []);
            $icon = Arr::get($name, "icon", "");
            $config = Arr::get($name, "config", []);
        }
        
        return new static($name, $info, $icon, $config);
    }
    
    /**
     * 设置服务提供者名称
     *
     * @param   string  $name 服务提供者名称
     * @return  object  $this
     */
    public function withName(string $name = "") 
    {
        $this->name = $name;
        
        return $this;
    }
    
    /**
     * 设置扩展信息
     *
     * @param   array   $info   扩展信息
     * @return  object  $this
     */
    public function withInfo(array $info = []) 
    {
        $this->info = array_merge($this->info, $info);
        
        return $this;
    }
    
    /**
     * 设置扩展配置
     *
     * @param   array   $config   扩展配置
     * @return  object  $this
     */
    public function withConfig(array $config = []) 
    {
        $this->config = array_merge($this->config, $config);
        
        return $this;
    }
    
    /**
     * 设置扩展图标
     *
     * @param   string  $icon   扩展图标
     * @return  object  $this
     */
    public function withIcon(string $icon = "") 
    {
        $this->icon = $icon;
        
        return $this;
    }

    /**
     * 获取服务提供者名称
     *
     * @return  string
     */
    public function getName() 
    {
        return $this->name;
    }

    /**
     * 获取信息
     *
     * @param  string $name    字段
     * @param  mixed  $default 默认值
     * @return \Illuminate\Support\Collection
     */
    public function getInfo(string $name = "", mixed $default = null) 
    {
        if (empty($name)) {
            return collect($this->info);
        }
        
        $data = Arr::get($this->info, $name, $default);
        
        return collect($data);
    }

    /**
     * 获取配置
     *
     * @param  string $name    字段
     * @param  mixed  $default 默认值
     * @return \Illuminate\Support\Collection
     */
    public function getConfig(string $name = "", mixed $default = null) 
    {
        if (empty($name)) {
            return collect($this->config);
        }
        
        $data = Arr::get($this->config, $name, $default);
        
        return collect($data);
    }

    /**
     * 获取扩展图标
     *
     * @return string
     */
    public function getIcon() 
    {
        return $this->icon;
    }

    /**
     * 返回服务提供者名称
     *
     * @return string
     */
    public function __toString() 
    {
        return $this->name;
    }
}
