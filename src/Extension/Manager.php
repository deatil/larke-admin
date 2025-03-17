<?php

declare (strict_types = 1);

namespace Larke\Admin\Extension;

use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Traits\Macroable;

use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Model\Extension as ExtensionModel;
use Larke\Admin\Composer\Resolve as ComposerResolve;
use Larke\Admin\Extension\ServiceProvider as ExtensionServiceProvider;

/**
 * 扩展
 *
 * @create 2020-10-30
 * @author deatil
 */
class Manager
{
    use Macroable;
    
    /**
     * @var string 扩展存放文件夹
     */
    protected string $extensionsDirectory = 'extension';
    
    /**
     * @var string 本地扩展缓存id
     */
    protected string $extensionsCacheId = 'larke-admin-local-extensions';
    
    /**
     * @var string 本地扩展缓存时间
     */
    protected int $extensionsCacheTime = 10080;
    
    /**
     * @var array<string, Info>
     */
    protected array $extensions = [];
    
    /**
     * @var string 默认图标
     */
    protected string $defaultIcon = __DIR__ . '/../../resources/icon/larke.png';
    
    /**
     * @var string 事件名称
     */
    protected string $eventBootingName = "larke-admin:booting";
    
    /**
     * @var string 事件名称
     */
    protected string $eventBootedName = "larke-admin:booted";

    /**
     * 构造函数
     *
     * @param string $extensionsDirectory
     * @param string $extensionsCacheId
     * @param int    $extensionsCacheTime
     */
    public function __construct(
        string $extensionsDirectory = 'extension', 
        string $extensionsCacheId   = 'larke-admin-local-extensions', 
        int    $extensionsCacheTime = 10080
    ) {
        $this->extensionsDirectory = $extensionsDirectory;
        $this->extensionsCacheId   = $extensionsCacheId;
        $this->extensionsCacheTime = $extensionsCacheTime;
    }

    /**
     * 添加扩展
     *
     * @param  string $name
     * @param  Info   $info
     * @return self
     */
    public function extend(string $name, Info $info = null): self
    {
        if (!empty($name) && !empty($info)) {
            $this->forget($name);
            
            $this->extensions[$name] = $info;
        }
        
        return $this;
    }
    
    /**
     * 获取添加的扩展
     *
     * @param string|array $name
     * @return Info|array|null
     */
    public function getExtend(mixed $name = null): mixed
    {
        if (is_array($name)) {
            $extensions = [];
            foreach ($name as $value) {
                $extensions[$name] = $this->getExtend($value);
            }
            
            return $extensions;
        }
        
        if (isset($this->extensions[$name])) {
            return $this->extensions[$name];
        }
        
        return null;
    }
    
    /**
     * 获取全部添加的扩展
     *
     * @return array
     */
    public function getAllExtend(): array
    {
        return $this->extensions;
    }
    
    /**
     * 移除添加的扩展
     *
     * @param string|array $name
     * @return Info|array|null
     */
    public function forget(mixed $name): mixed
    {
        if (is_array($name)) {
            $forgetExtensions = [];
            foreach ($name as $value) {
                $forgetExtensions[$value] = $this->forget($value);
            }
            
            return $forgetExtensions;
        }
        
        if (isset($this->extensions[$name])) {
            $extension = $this->extensions[$name];
            unset($this->extensions[$name]);
            
            return $extension;
        }
        
        return null;
    }
    
    /**
     * 检测非 compoer 扩展是否存在
     *
     * @param string $name 扩展包名
     * @return bool
     */
    public function checkLocal(string $name): bool
    {
        $extensionDirectory = $this->getExtensionPath($name);
        
        if (File::exists($extensionDirectory)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * composer 安装语句
     *
     * @param string $name 扩展包名
     * @return string
     */
    public function composerRequireCommand(string $name): string
    {
        $directory = $this->getExtensionPath($name);
        if (! File::exists($directory)) {
            return '';
        }
        
        $extensionDirectory = $this->getExtensionDirectory();
        $path = $extensionDirectory . '/' . $name;
        
        $command = sprintf(
            'composer config repositories.%s path %s && composer require %s',
            $name,
            $path,
            $name
        );
        
        return $command;
    }
    
    /**
     * composer 卸载语句
     *
     * @param string $name 扩展包名
     * @return string
     */
    public function composerRemoveCommand(string $name): string
    {
        $directory = $this->getExtensionPath($name);
        if (! File::exists($directory)) {
            return '';
        }
        
        $extensionDirectory = $this->getExtensionDirectory();
        $path = $extensionDirectory . '/' . $name;
        
        $command = sprintf(
            'composer remove %s && composer config --unset repositories.%s', 
            $name,
            $name
        );
        
        return $command;
    }

    /**
     * @param callable $callback
     */
    public function booting(callable $callback): void
    {
        Event::listen($this->eventBootingName, $callback);
    }

    /**
     * @param callable $callback
     */
    public function booted(callable $callback): void
    {
        Event::listen($this->eventBootedName, $callback);
    }

    /**
     * @return void
     */
    public function callBooting(): void
    {
        Event::dispatch($this->eventBootingName);
    }

    /**
     * @return void
     */
    public function callBooted(): void
    {
        Event::dispatch($this->eventBootedName);
    }
    
    /**
     * 设置扩展后台路由
     *
     * @param callable $callback
     * @param array    $config
     * @return object $this
     */
    public function routes(callable $callback, array $config = []): self
    {
        $attributes = array_merge(
            [
                'domain'     => config('larkeadmin.route.domain'),
                'prefix'     => config('larkeadmin.route.prefix'),
                'middleware' => config('larkeadmin.route.middleware'),
            ],
            $config
        );

        Route::group($attributes, $callback);
        
        return $this;
    }
    
    /**
     * 注册命名空间
     *
     * @param mixed $prefix
     * @param mixed $paths
     * @return object $this
     */
    public function namespaces(mixed $prefix, mixed $paths = []): self
    {
        app('larke-admin.loader')
            ->setPsr4($prefix, $paths)
            ->register();
        
        return $this;
    }
    
    /**
     * 添加登陆过滤
     *
     * @param array $excepts
     * @return void
     */
    public function authenticateExcepts(array $excepts = []): void
    {
        if (empty($excepts)) {
            return ;
        }
        
        $authenticateExcepts = config('larkeadmin.auth.authenticate_excepts', []);
        foreach ($excepts as $except) {
            $authenticateExcepts[] = $except;
        }
        
        config([
            'larkeadmin.auth.authenticate_excepts' => $authenticateExcepts,
        ]);
    }
    
    /**
     * 添加权限过滤
     *
     * @param array $excepts
     * @return void
     */
    public function permissionExcepts(array $excepts = []): void
    {
        if (empty($excepts)) {
            return ;
        }
        
        $permissionExcepts = config('larkeadmin.auth.permission_excepts', []);
        foreach ($excepts as $except) {
            $permissionExcepts[] = $except;
        }
        
        config([
            'larkeadmin.auth.permission_excepts' => $permissionExcepts,
        ]);
    }
    
    /**
     * 加载扩展
     *
     * @return void
     */
    public function bootExtension(): void
    {
        // 数据库检测
        try {
            $list = ExtensionModel::getExtensions();
        } catch(\Exception $e) {
            return;
        }
        
        $extensionDirectory = $this->getExtensionPath();
        
        $services = collect($list)->map(function($data) use($extensionDirectory) {
            if ($data['status'] != 1) {
                return null;
            }

            if (empty($data['name'])) {
                return null;
            }
            
            // 扩展绑定类
            if (empty($data['class_name'])) {
                return null;
            }
            
            $directory = $extensionDirectory 
                . DIRECTORY_SEPARATOR . $data['name'];
            
            if (! class_exists($data['class_name']) 
                && File::exists($directory)
            ) {
                // 绑定非composer扩展
                $composer = ComposerResolve::create()->withDirectory($directory);
                $cacheId = md5(str_replace('\\', '/', $data['name']));
                
                $composerData = Cache::get($cacheId);
                if (! $composerData) {
                    $composerData = $composer->getData();
                    Cache::put($cacheId, $composerData, $this->extensionsCacheTime);
                }
                
                $composer->registerAutoload(Arr::get($composerData, 'autoload', []));
                
                // 加载 dev 数据
                $composer->registerAutoload(Arr::get($composerData, 'autoload-dev', []));
                
                $composer->registerProvider(Arr::get($composerData, 'providers', []));
                $composer->registerAlias(Arr::get($composerData, 'aliases', []));
            }
            
            if (! class_exists($data['class_name'])) {
                return null;
            }
            
            $newClass = app()->register($data['class_name']);
            if (! $newClass) {
                return null;
            }
            
            return $newClass;
        })->filter(function($data) {
            return !empty($data);
        })->toArray();
        
        array_walk($services, function ($s) {
            $this->startService($s);
        });
    }
    
    /**
     * 启动扩展服务
     *
     * @return void
     */
    protected function startService(ExtensionServiceProvider $service): void
    {
        $service->callStartingCallbacks();

        if (method_exists($service, 'start')) {
            app()->call([$service, 'start']);
        }

        $service->callStartedCallbacks();
    }
    
    /**
     * 加载本地扩展
     *
     * @return object $this
     */
    public function loadExtension(): self
    {
        $extensions = Cache::get($this->extensionsCacheId);
        if (! $extensions) {
            $directory = $this->getExtensionPath();
            $directories = $this->getDirectories($directory);
            
            $extensions = collect($directories)
                ->map(function($path) {
                    $composerData = ComposerResolve::create()
                        ->withDirectory($path)
                        ->getData();
                    
                    $name = Arr::get($composerData, 'info.name', '');
                    
                    $package = basename($path);
                    $vendor = basename(dirname($path));
                    
                    if (empty($name) || $vendor . '/' . $package != $name) {
                        return [];
                    }

                    return $composerData;
                })
                ->filter(function ($data) {
                    return !empty($data);
                })
                ->values()
                ->toArray();
            
            Cache::put($this->extensionsCacheId, $extensions, $this->extensionsCacheTime);
        }
        
        $composer = ComposerResolve::create();
        collect($extensions)->each(function($extension) use($composer) {
            $providers = Arr::get($extension, 'providers', []);
            
            $composer->registerAutoload(Arr::get($extension, 'autoload', []));
            
            // 加载 dev 数据
            $composer->registerAutoload(Arr::get($extension, 'autoload-dev', []));
            
            $composer->registerProvider(Arr::get($extension, 'providers', []));
            $composer->registerAlias(Arr::get($extension, 'aliases', []));
        });
        
        return $this;
    }
    
    /**
     * 刷新本地加载扩展
     *
     * @return object $this
     */
    public function refresh(): self
    {
        Cache::forget($this->extensionsCacheId);
        
        return $this;
    }
    
    /**
     * 移除扩展信息缓存
     *
     * @param string $name
     * @return object $this
     */
    public function forgetExtensionCache(string $name): self
    {
        // 清除缓存
        $cacheId = md5(str_replace('\\', '/', $name));
        Cache::forget($cacheId);
        
        return $this;
    }
    
    /**
     * 扩展存放文件夹
     *
     * @return string
     */
    public function getExtensionDirectory(): string
    {
        return $this->extensionsDirectory;
    }
    
    /**
     * 扩展存放目录
     *
     * @param string $path
     * @return string
     */
    public function getExtensionPath(string $path = ''): string
    {
        $extensionPath = base_path($this->getExtensionDirectory());
        
        return $extensionPath.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    
    /**
     * 扩展绑定类
     *
     * @param string|null $name
     * @return string
     */
    public function getExtensionClass(?string $name = null): string
    {
        if (empty($name)) {
            return '';
        }
        
        $info = Arr::get($this->extensions, $name, '');
        if (empty($info)) {
            return '';
        }
        
        $className = $info->getName();
        
        return $className;
    }
    
    /**
     * 实例化类
     *
     * @param string|null $className
     * @return mixed
     */
    public function newClass(?string $className = null): mixed
    {
        if (! class_exists($className)) {
            return false;
        }
        
        $newClass = app()->register($className);
        if (! ($newClass instanceof ExtensionServiceProvider)) {
            return false;
        }
        
        return $newClass;
    }
    
    /**
     * 调用类方法
     *
     * @param string|null $className 
     * @param string|null $method 
     * @param array $param 
     * @return mixed
     */
    public function callClassMethod(?string $className = null, ?string $method = null, array $param = []): mixed
    {
        if (empty($className) || empty($method)) {
            return false;
        }
        
        $newClass = $this->newClass($className);
        if (! $newClass) {
            return false;
        }
        
        if (! method_exists($newClass, $method)) {
            return false;
        }
        
        $res = call_user_func_array([$newClass, $method], $param);
        return $res;
    }
    
    /**
     * 实例化扩展的类
     *
     * @param string|null $name
     * @return mixed
     */
    public function newExtensionClass(?string $name = null): mixed
    {
        $className = $this->getExtensionClass($name);
        
        return $this->newClass($className);
    }
    
    /**
     * 扩展信息
     *
     * @param string|null $name
     * @return array
     */
    public function getExtension(?string $name = null): array
    {
        $data = $this->getExtend($name);
        if (empty($data)) {
            return [];
        }
        
        // 扩展信息
        $info = $data->getInfo()->toArray();
        
        // 配置
        $config = $data->getConfig()->toArray();
        
        // 扩展图标
        $icon = $data->getIcon();
        $icon = $this->getIcon($icon);
        
        // 服务提供者名称
        $className = $data->getName();
        
        return [
            'name'        => $name,
            'title'       => Arr::get($info, 'title'),
            'description' => Arr::get($info, 'description'),
            'keywords'    => Arr::get($info, 'keywords'),
            'homepage'    => Arr::get($info, 'homepage'),
            'authors'     => Arr::get($info, 'authors', []), 
            'version'     => Arr::get($info, 'version'),
            'adaptation'  => Arr::get($info, 'adaptation'),
            'require'     => Arr::get($info, 'require', []),
            'order'       => Arr::get($info, 'order', 100),
            'config'      => $config,
            'icon'        => $icon,
            'class_name'  => $className,
        ];
    }
    
    /**
     * 扩展配置信息
     *
     * @param string|null $name
     * @return array
     */
    public function getExtensionConfig(?string $name = null): array
    {
        $info = $this->getExtension($name);
        if (empty($info)) {
            return [];
        }
        
        if (empty($info['config'])) {
            return [];
        }
        
        return $info['config'];
    }
    
    /**
     * 全部添加的扩展
     *
     * @return array
     */
    public function getExtensions(): array
    {
        $extensions = $this->extensions;
        
        $thiz = $this;
        
        $list = collect($extensions)->map(function($className, $name) use($thiz) {
            $info = $thiz->getExtension($name);
            if (!empty($info)) {
                return $info;
            }
        })->filter(function($data) {
            return !empty($data);
        })->toArray();
        
        return $list;
    }
    
    /**
     * 扩展标识图片
     *
     * @param string|null $icon
     * @return string
     */    
    public function getIcon(?string $icon = null): string
    {
        if (! File::exists($icon) || ! File::isFile($icon)) {
            $icon = $this->defaultIcon;
        }
        
        $data = File::get($icon);
        $base64Data = base64_encode($data);
        
        $iconData = "data:image/png;base64,{$base64Data}";
        
        return $iconData;
    }
    
    /**
     * 验证扩展信息
     *
     * @param array $info
     * @return boolen
     */
    public function validateInfo(array $info): bool
    {
        $mustInfo = [
            'title',
            'description',
            'keywords',
            'authors',
            'version',
            'adaptation',
        ];
        if (empty($info)) {
            return false;
        }
        
        return !collect($mustInfo)
            ->contains(function ($key) use ($info) {
                return (!isset($info[$key]) || empty($info[$key]));
            });
    }
    
    /**
     * 获取满足条件的扩展文件夹
     *
     * @param string|null $dirPath
     * @return array
     */
    public function getDirectories(?string $dirPath = null): array
    {
        $extensions = [];
        
        if (empty($dirPath) || ! is_dir($dirPath)) {
            return $extensions;
        }

        $it = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::FOLLOW_SYMLINKS)
        );
        $it->setMaxDepth(2);
        $it->rewind();

        while ($it->valid()) {
            if ($it->getDepth() > 1 
                && $it->isFile()
                && $it->getFilename() === 'composer.json'
            ) {
                $extensions[] = dirname($it->getPathname());
            }

            $it->next();
        }

        return $extensions;
    }
    
    /**
     * 根据类名获取类所在文件夹
     *
     * @param string|object $class
     * @return string
     */
    public function getPathFromClass($class): string
    {
        if (is_object($class)) {
            $class = get_class($class);
        }
        
        $reflection = new ReflectionClass($class);
        $filePath = dirname($reflection->getFileName());

        return $filePath;
    }
    
}
