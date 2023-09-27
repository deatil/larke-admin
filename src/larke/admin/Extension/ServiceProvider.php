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
     * @param string $pkgName 扩展包名
     * @param Info   $info    扩展信息
     */
    protected function withExtension(string $pkgName, Info $info)
    {
        AdminExtension::extend($pkgName, $info);
    }

    /**
     * 添加扩展
     *
     * @param string $name         服务提供者名称
     * @param string $composerFile composer.json 文件
     * @param string $icon         扩展图标
     * @param array  $config       扩展配置
     */
    protected function withExtensionFromComposer(
        string $name         = null,
        string $composerFile = '',
        string $icon         = '',
        array  $config       = []
    ) {
        $info = $this->fromComposer($composerFile);
        
        // 扩展包名
        $pkgName = Arr::get($info, 'name', "");
        if (empty($pkgName)) {
            return ;
        }
        
        $this->withExtension(
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
     * 设置命名空间
     *
     * @param mixed $prefix
     * @param mixed $paths
     */
    protected function withNamespace(mixed $prefix, mixed $paths = [])
    {
        AdminExtension::namespaces($prefix, $paths);
    }

    /**
     * 设置扩展路由
     *
     * @param callable $callback
     * @param array    $config
     */
    protected function withRoute(callable $callback, array $config = [])
    {
        AdminExtension::routes($callback, $config);
    }

    /**
     * 添加登陆过滤
     *
     * @param array $excepts
     */
    protected function withAuthenticateExcepts(array $excepts = [])
    {
        AdminExtension::authenticateExcepts($excepts);
    }

    /**
     * 添加权限过滤
     *
     * @param array $excepts
     */
    protected function withPermissionExcepts(array $excepts = [])
    {
        AdminExtension::permissionExcepts($excepts);
    }

    /**
     * 生成扩展信息
     *
     * @param  string|array $name   服务提供者名称
     * @param  array        $info   扩展信息
     * @param  string       $icon   扩展图标
     * @param  array        $config 扩展配置
     * @return Info         
     */
    protected function makeExtensionInfo(
        $name = null, 
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
    protected function fromComposer(string $composerFile, bool $isOriginal = false) 
    {
        $data = Composer::parse($composerFile)->toArray();
        
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
