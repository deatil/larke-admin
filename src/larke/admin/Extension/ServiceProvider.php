<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

use Larke\Admin\Traits\ExtensionService as ExtensionServiceTrait;

/*
 * 扩展服务提供者
 *
 * @create 2020-10-30
 * @author deatil
 */
abstract class ServiceProvider extends LaravelServiceProvider
{    
    use ExtensionServiceTrait;
    
    /**
     * 扩展信息
     */
    protected $info = [
        'name' => '', // 扩展ID名称
        'title' => '', // 扩展名称
        'introduce' => '', // 扩展描述
        'author' => '', // 作者
        'authorsite' => '', // 作者网站[选填]
        'authoremail' => '', // 作者邮箱[选填]
        'version' => '1.0.0', // 版本号
        'adaptation' => '^1.0', // 适配系统版本
        
        // 依赖扩展[选填]
        'require' => [
            // 'Extension2' => '1.2.*',
        ], 
    ];
    
    /**
     * 扩展配置，选填
     */
    public $config = [];
    
    /**
     * 启动，只有启用后加载
     */
    public function start()
    {}
    
    /**
     * 安装后
     */
    public function install()
    {}
    
    /**
     * 卸载后
     */
    public function uninstall()
    {}
    
    /**
     * 更新后
     */
    public function upgrade()
    {}
    
    /**
     * 启用后
     */
    public function enable()
    {}
    
    /**
     * 禁用后
     */
    public function disable()
    {}
}
