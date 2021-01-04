<?php

declare (strict_types = 1);

namespace Larke\Admin;

use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

use Larke\Admin\Service\Composer;
use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Model\Extension as ExtensionModel;
use Larke\Admin\Extension\ServiceProvider as ExtensionServiceProvider;

/*
 * 扩展
 *
 * @create 2020-10-30
 * @author deatil
 */
class Extension
{
    /**
     * @var array
     */
    public $extensions = [];

    /**
     * Extend a extension.
     *
     * @param string $class
     *
     * @return bool
     */
    public function extend($name, $class = null)
    {
        if (isset($this->extensions[$name])) {
            return $this;
        }
        
        if (empty($class)) {
            return $this;
        }
        
        $this->extensions[$name] = $class;
        
        return $this;
    }
    
    /**
     * Get a extension.
     *
     * @param string|array $name
     *
     * @return string|array
     */
    public function getExtend($names = null)
    {
        if (is_array($names)) {
            $extensions = [];
            foreach ($names as $name) {
                $extensions[$name] = $this->getExtend($name);
            }
            
            return $extensions;
        }
        
        if (isset($this->extensions[$names])) {
            return $this->extensions[$names];
        }
        
        return $this->extensions;
    }
    
    /**
     * Forget a extension or extensions.
     *
     * @param string|array $name
     *
     * @return string|array
     */
    public function forget($names)
    {
        if (is_array($names)) {
            $forgetExtensions = [];
            foreach ($names as $name) {
                $forgetExtensions[$name] = $this->forget($name);
            }
            
            return $forgetExtensions;
        }
        
        if (isset($this->extensions[$names])) {
            $extension = $this->extensions[$names];
            unset($this->extensions[$names]);
            return $extension;
        }
        
        return null;
    }
    
    /**
     * Set routes for this Route.
     *
     * @param $callback
     * @param $config
     * 
     * @return self
     */
    public function routes($callback, $config = [])
    {
        $attributes = array_merge(
            [
                'prefix' => config('larkeadmin.route.prefix'),
                'middleware' => config('larkeadmin.route.middleware'),
            ],
            $config
        );

        Route::group($attributes, $callback);
        
        return $this;
    }
    
    /**
     * Set namespaces.
     *
     * @param $prefix
     * @param $paths
     * 
     * @return self
     */
    public function namespaces($prefix, $paths = [])
    {
        app('larke.admin.loader')->setPsr4($prefix, $paths)->register();
        
        return $this;
    }
    
    /**
     * Boot Extension.
     *
     * @return void
     */
    public function bootExtension()
    {
        if (! Schema::hasTable((new ExtensionModel)->getTable())) {
            return ;
        }
        
        $list = ExtensionModel::getExtensions();
        $extensionDirectory = $this->getExtensionDirectory();
        
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
            
            $composer = Composer::create()->withDirectory($directory);
            $cacheId = md5(str_replace('\\', '/', $data['name']));
            
            $composerData = Cache::get($cacheId);
            if (! $composerData) {
                $composerData = $composer->getData();
                Cache::put($cacheId, $composerData, 10080);
            }
            
            $composer->registerAutoload($composerData['autoload']);
            
            // 加载dev数据
            if (config('app.debug')) {
                $composer->registerAutoload($composerData['autoload-dev']);
            }
            
            $composer->registerProvider($composerData['providers']);
            
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
     * Boot the given service.
     *
     * @return void
     */
    protected function startService(ExtensionServiceProvider $service)
    {
        $service->callStartingCallbacks();

        if (method_exists($service, 'start')) {
            app()->call([$service, 'start']);
        }

        $service->callStartedCallbacks();
    }
    
    /**
     * Load extensions.
     *
     * @return object $this
     */
    public function loadExtension()
    {
        $directory = $this->getExtensionDirectory();
        
        $directories = $this->getDirectories($directory);
        
        collect($directories)->each(function($path) use($directory) {
            $composer = Composer::create()->withDirectory($path);
            
            $cacheId = Str::replaceLast(realpath($directory), '', realpath($path));
            $cacheId = md5(ltrim(str_replace('\\', '/', $cacheId), '/'));
            
            $composerData = Cache::get($cacheId);
            if (! $composerData) {
                $composerData = $composer->getData();
                Cache::put($cacheId, $composerData, 10080);
            }
            
            $composer->registerAutoload($composerData['autoload']);
            
            // 加载dev数据
            if (config('app.debug')) {
                $composer->registerAutoload($composerData['autoload-dev']);
            }
            
            $composer->registerProvider($composerData['providers']);
        });
        
        return $this;
    }
    
    /**
     * Forget extension cache
     *
     * @param string $name
     *
     * @return object $this
     */
    public function forgetExtensionCache(string $name)
    {
        // 清除缓存
        $cacheId = md5(str_replace('\\', '/', $name));
        $composerData = Cache::forget($cacheId);
        
        return $this;
    }
    
    /**
     * Get extensions directory.
     *
     * @param string $path
     *
     * @return string
     */
    public function getExtensionDirectory(string $path = '')
    {
        $extensionDirectory =  config('larkeadmin.extension.directory');
        return $extensionDirectory.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    
    /**
     * Get extension class.
     *
     * @param string|null $name
     *
     * @return string
     */
    public function getExtensionClass(?string $name = null)
    {
        if (empty($name)) {
            return '';
        }
        
        $className = Arr::get($this->extensions, $name, '');
        
        return $className;
    }
    
    /**
     * Get new class.
     *
     * @param string|null $className
     *
     * @return object
     */
    public function getNewClass(?string $className = null)
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
     * Get new class.
     *
     * @param string|null $className 
     * @param string|null $method 
     * @param array $param 
     *
     * @return mixed
     */
    public function getNewClassMethod(?string $className = null, ?string $method = null, array $param = [])
    {
        if (empty($className) || empty($method)) {
            return false;
        }
        
        $newClass = $this->getNewClass($className);
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
     * Get extension new class.
     *
     * @param string|null $name
     *
     * @return mixed|object
     */
    public function getExtensionNewClass(?string $name = null)
    {
        $className = $this->getExtensionClass($name);
        
        return $this->getNewClass($className);
    }
    
    /**
     * Get extension info.
     *
     * @param string|null $name
     *
     * @return array
     */
    public function getExtension(?string $name = null)
    {
        $newClass = $this->getExtensionNewClass($name);
        if ($newClass === false) {
            return [];
        }
        
        if (! isset($newClass->info)) {
            return [];
        }
        
        $info = $newClass->info;
        
        // 配置
        $config = [];
        if (isset($newClass->config)) {
            $config = (array) $newClass->config;
        }
        
        return [
            'name' => $name,
            'title' => Arr::get($info, 'title'),
            'description' => Arr::get($info, 'description'),
            'keywords' => Arr::get($info, 'keywords'),
            'homepage' => Arr::get($info, 'homepage'),
            'authors' => Arr::get($info, 'authors', []), 
            'version' => Arr::get($info, 'version'),
            'adaptation' => Arr::get($info, 'adaptation'),
            'require' => Arr::get($info, 'require', []),
            'config' => $config,
            'class_name' => Arr::get($this->extensions, $name, ''),
        ];
    }
    
    /**
     * Get extension config.
     *
     * @param string|null $name
     *
     * @return array
     */
    public function getExtensionConfig(?string $name = null)
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
     * Get extensions.
     *
     * @return array
     */
    public function getExtensions()
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
     * validateInfo.
     *
     * @param array $info
     *
     * @return boolen
     */
    public function validateInfo(array $info)
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
     * get directories.
     *
     * @param string|null $dirPath
     *
     * @return array
     */
    public function getDirectories(?string $dirPath = null)
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
            if ($it->getDepth() > 1 && $it->getFilename() === 'composer.json') {
                $extensions[] = dirname($it->getPathname());
            }

            $it->next();
        }

        return $extensions;
    }
    
    /**
     * get path from class
     *
     * @param string|null $class
     *
     * @return string|bool
     */
    public function getPathFromClass(?string $class = null)
    {
        $reflection = new ReflectionClass(get_class($class));
        $filePath = dirname($reflection->getFileName());

        return $filePath;
    }
    
}
