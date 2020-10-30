<?php

namespace Larke\Admin\Extension;

/*
 * 扩展服务提供者
 *
 * @create 2020-10-30
 * @author deatil
 */
abstract class ServiceProvider
{
    /**
     * 扩展信息
     */
    public $info = [
        'name' => '',
        'title' => '',
        'introduce' => '',
        'author' => '', 
        'authorsite' => '', // 选填
        'authoremail' => '', // 选填
        'version' => '1.0.0',
        'adaptation' => '1.0.0',
        'need_module' => [], // 选填
        'setting' => [], // 选填
    ];
    
    /**
     * 注册，只有启用后加载
     */
    public function register()
    {}
    
    /**
     * 引导，只有启用后加载
     */
    public function boot()
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
    public function Upgrade()
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
