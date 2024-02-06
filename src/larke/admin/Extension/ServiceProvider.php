<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use Closure;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Traits\Macroable;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

use Larke\Admin\Composer\Composer;
use Larke\Admin\Event as AdminEvent;
use Larke\Admin\Facade\Extension as AdminExtension;
use Larke\Admin\Traits\ExtensionServiceProvider as ExtensionServiceProviderTrait;

/*
 * 扩展服务提供者
 *
 * @create 2020-10-30
 * @author deatil
 */
abstract class ServiceProvider extends BaseServiceProvider
{
    use Macroable, 
        ExtensionServiceProviderTrait;

    /**
     * 启动，只有启用后加载
     */
    public function start()
    {
        // 业务代码
    }

    /**
     * 添加扩展
     *
     * @param string $name     服务提供者名称，通常为设置时取用 __CLASS__
     * @param string $composer 扩展 composer.json 文件
     * @param string $icon     扩展图标
     * @param array  $config   扩展配置
     */
    protected function addExtension(
        string $name     = '',
        string $composer = '',
        string $icon     = '',
        array  $config   = [],
    ) {
        $info = $this->fromComposer($composer);
        
        // 扩展包名
        $pkgName = Arr::get($info, 'name', "");
        if (empty($pkgName)) {
            return ;
        }
        
        $this->registerExtension(
            $pkgName,
            $this->makeExtensionInfo(
                $name, 
                $info, 
                $icon, 
                $config
            )
        );
    }

    /**
     * 添加路由
     *
     * @param callable $callback
     * @param array    $config
     */
    protected function addRoute(callable $callback, array $config = [])
    {
        AdminExtension::routes($callback, $config);
    }

    /**
     * 添加登陆过滤
     *
     * @param array $excepts
     */
    protected function addAuthenticateExcepts(array $excepts = [])
    {
        AdminExtension::authenticateExcepts($excepts);
    }

    /**
     * 添加权限过滤
     *
     * @param array $excepts
     */
    protected function addPermissionExcepts(array $excepts = [])
    {
        AdminExtension::permissionExcepts($excepts);
    }

    /**
     * 注册新命名空间
     *
     * @param mixed $prefix
     * @param mixed $paths
     */
    protected function registerNamespace(mixed $prefix, mixed $paths = [])
    {
        AdminExtension::namespaces($prefix, $paths);
    }

    /**
     * 注册扩展
     *
     * @param string $name 扩展包名
     * @param Info   $info 扩展信息
     */
    protected function registerExtension(string $name, Info $info)
    {
        AdminExtension::extend($name, $info);
    }

    /**
     * 生成扩展信息
     *
     * @param  string $name   服务提供者名称
     * @param  array  $info   扩展信息
     * @param  string $icon   扩展图标
     * @param  array  $config 扩展配置
     * @return Info 
     */
    protected function makeExtensionInfo(
        string $name   = '',
        array  $info   = [],
        string $icon   = '',
        array  $config = []
    ) {
        return Info::make($name, $info, $icon, $config);
    }

    /**
     * 从 composer.json 获取数据
     *
     * @return array
     */
    protected function fromComposer(string $file, bool $isOriginal = false) 
    {
        $data = Composer::parse($file)->toArray();
        
        if (! $isOriginal) {
            $extensionData = Arr::get($data, "extra.larke", []);
            $data = array_merge($data, $extensionData);
        }
        
        return $data;
    }
    
    /**
     * 安装后
     */
    protected function onInatll(Closure $callback)
    {
        Event::listen(function (AdminEvent\ExtensionInstall $event) use($callback) {
            $callback($event->name, $event->info);
        });
    }
    
    /**
     * 卸载后
     */
    protected function onUninstall(Closure $callback)
    {
        Event::listen(function (AdminEvent\ExtensionUninstall $event) use($callback) {
            $callback($event->name, $event->info);
        });
    }
    
    /**
     * 更新后
     */
    protected function onUpgrade(Closure $callback)
    {
        Event::listen(function (AdminEvent\ExtensionUpgrade $event) use($callback) {
            $callback($event->name, $event->oldInfo, $event->newInfo);
        });
    }
    
    /**
     * 启用后
     */
    protected function onEnable(Closure $callback)
    {
        Event::listen(function (AdminEvent\ExtensionEnable $event) use($callback) {
            $callback($event->name, $event->info);
        });
    }
    
    /**
     * 禁用后
     */
    protected function onDisable(Closure $callback)
    {
        Event::listen(function (AdminEvent\ExtensionDisable $event) use($callback) {
            $callback($event->name, $event->info);
        });
    }
}
