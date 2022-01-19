<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

use Larke\Admin\Traits\ExtensionServiceProvider as ExtensionServiceProviderTrait;

/*
 * 扩展服务提供者
 *
 * @create 2020-10-30
 * @author deatil
 */
abstract class ServiceProvider extends LaravelServiceProvider
{    
    use Macroable, 
        ExtensionServiceProviderTrait;
    
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
     * 扩展配置[选填]
     */
    public $config = [];
    
    /**
     * 扩展图标
     */
    public $icon = '';
    
    /**
     * 启动，只有启用后加载
     */
    public function start()
    {}
}
