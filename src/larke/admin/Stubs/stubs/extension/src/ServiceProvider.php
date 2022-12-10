<?php

namespace {namespace};

use Illuminate\Support\Facades\Artisan;

use Larke\Admin\Extension\Rule;
use Larke\Admin\Extension\Menu;
use Larke\Admin\Extension\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * composer
     */
    public $composer = __DIR__ . '/../composer.json';

    /**
     * 配置[选填]
     */
    protected $config = [
        [
            'name' => 'atext',
            'title' => '文本',
            'type' => 'text',
            'value' => '文本',
            'require' => '1',
            'description' => '设置内容文本',
        ],
        [
            'name' => 'atextarea',
            'title' => '文本框',
            'type' => 'textarea',
            'value' => '文本框',
            'require' => '1',
            'description' => '设置内容文本框',
        ],
    ];
    
    /**
     * 扩展图标
     */
    protected $icon = __DIR__ . '/../logo.png';
    
    // 包名
    protected $pkgName = "{authorName}/{extensionName}";
    
    protected $slug = 'larke-admin.ext.{extensionName}';
    
    /**
     * 启动
     */
    public function boot()
    {
        // 扩展注册
        $this->withExtensionFromComposer(
            __CLASS__, 
            $this->composer,
            $this->icon,
            $this->config
        );
        
        // 事件
        $this->bootListeners();
    }
    
    /**
     * 开始，只有启用后加载
     */
    public function start()
    {
        $this->commands([
            Command\Cmd::class,
        ]);
        
        // 路由
        $this->loadRoutesFrom(__DIR__ . '/../resources/route/admin.php');
    }
    
    /**
     * 推送
     */
    protected function assetsPublishes()
    {
        $this->publishes([
            __DIR__.'/../resources/assets' => public_path('extension/{authorName}/{extensionName}'),
        ], '{authorName}-{extensionName}-assets');
        
        Artisan::call('vendor:publish', [
            '--tag' => '{authorName}-{extensionName}-assets',
            '--force' => true,
        ]);
    }
    
    /**
     * 监听器
     */
    public function bootListeners()
    {
        $thiz = $this;
        
        // 安装后
        $this->onInatll(function ($name, $info) use($thiz) {
            if ($name == $thiz->pkgName) {
                $thiz->install();
            }
        });
        
        // 卸载后
        $this->onUninstall(function ($name, $info) use($thiz) {
            if ($name == $thiz->pkgName) {
                $thiz->uninstall();
            }
        });
        
        // 更新后
        $this->onUpgrade(function ($name, $oldInfo, $newInfo) use($thiz) {
            if ($name == $thiz->pkgName) {
                $thiz->upgrade();
            }
        });
        
        // 启用后
        $this->onEnable(function ($name, $info) use($thiz) {
            if ($name == $thiz->pkgName) {
                $thiz->enable();
            }
        });
        
        // 禁用后
        $this->onDisable(function ($name, $info) use($thiz) {
            if ($name == $thiz->pkgName) {
                $thiz->disable();
            }
        });
    }
    
    /**
     * 安装后
     */
    protected function install()
    {
        $slug = $this->slug;
        
        $rules = include __DIR__ . '/../resources/rules/rules.php';
        
        // 添加权限
        Rule::create($rules);
        
        // 添加菜单
        Menu::create($rules);

        $this->assetsPublishes();
    }
    
    /**
     * 卸载后
     */
    protected function uninstall()
    {
        // 删除权限
        Rule::delete($this->slug);
        
        // 删除菜单
        Menu::delete($this->slug);
    }
    
    /**
     * 更新后
     */
    protected function upgrade()
    {}
    
    /**
     * 启用后
     */
    protected function enable()
    {
        // 启用权限
        Rule::enable($this->slug);
        
        // 启用菜单
        Menu::enable($this->slug);
    }
    
    /**
     * 禁用后
     */
    protected function disable()
    {
        // 禁用权限
        Rule::disable($this->slug);
        
        // 禁用菜单
        Menu::disable($this->slug);
    }

}
