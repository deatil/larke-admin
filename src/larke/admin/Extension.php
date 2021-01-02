<?php

declare (strict_types = 1);

namespace Larke\Admin;

use ReflectionClass;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

use Larke\Admin\Support\Composer;
use Larke\Admin\Support\ClassMapGenerator;
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

            if (empty($data['class_name'])) {
                return null;
            }

            if (empty($data['name'])) {
                return null;
            }
            
            $directory = $extensionDirectory 
                . DIRECTORY_SEPARATOR . $data['name'];
            $this->registerAutoload($directory);
            
            // 加载dev数据
            if (config('app.debug')) {
                $this->registerAutoload($directory, 'autoload-dev');
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
     * @return $this
     */
    public function loadExtension()
    {
        $directory = $this->getExtensionDirectory();
        
        $directories = $this->getDirectories($directory);
        
        collect($directories)->each(function($directory) {
            $this->registerAutoload($directory);
            
            // 加载dev数据
            if (config('app.debug')) {
                $this->registerAutoload($directory, 'autoload-dev');
            }
        });
        
        return $this;
    }
    
    /**
     * Get extensions directory.
     *
     * @param string
     *
     * @return string
     */
    public function getExtensionDirectory($path = '')
    {
        $extensionDirectory =  config('larkeadmin.extension.directory');
        return $extensionDirectory.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    
    /**
     * Get extension class.
     *
     * @param string
     *
     * @return string
     */
    public function getExtensionClass($name = null)
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
     * @param string
     */
    public function getNewClass($className = null)
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
     * @param $className string
     * @param $method string
     * @param $param array
     *
     * @return mixed
     */
    public function getNewClassMethod($className = null, $method = null, $param = [])
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
     * @param string
     *
     * @return mixed|object
     */
    public function getExtensionNewClass($name = null)
    {
        $className = $this->getExtensionClass($name);
        
        return $this->getNewClass($className);
    }
    
    /**
     * Get extension info.
     *
     * @param $name string
     *
     * @return array
     */
    public function getExtension($name = null)
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
     * @param $name string
     *
     * @return array
     */
    public function getExtensionConfig($name = null)
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
     * @param array
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
     * Get composer info.
     *
     * @param string $directory
     *
     * @return object $this
     */
    public function composer($composer)
    {
        return Composer::parse($composer);
    }
    
    /**
     * Register autoload.
     *
     * @param string $directory
     *
     * @return object $this
     */
    public function registerAutoload($directory = null, $autoload = 'autoload')
    {
        $composerProperty = $this->composer($directory.'/composer.json');

        $psr0 = $composerProperty->get($autoload.'.psr-0');
        $psr4 = $composerProperty->get($autoload.'.psr-4');
        $classmap = $composerProperty->get($autoload.'.classmap');
        $files = $composerProperty->get($autoload.'.files');
        $exclude = $composerProperty->get($autoload.'.exclude-from-classmap');

        $classLoader = app('larke.admin.loader');
        
        if (! empty($psr0)) {
            foreach ($psr0 as $namespace0 => $path0) {
                $path0 = $directory.'/'.trim($path0, '/').'/';

                $classLoader->add($namespace0, $path0);
            }
        }
        
        if (! empty($psr4)) {
            foreach ($psr4 as $namespace => $path) {
                $path = $directory.'/'.trim($path, '/').'/';

                $classLoader->addPsr4($namespace, $path);
            }
        }
        
        $excluded = ClassMapGenerator::excluded($exclude);
        if (! empty($classmap)) {
            $classmapId = md5($directory.$autoload);
            
            $classmaps = Cache::get($classmapId);
            if (! $classmaps) {
                $classmaps = [];
                foreach ($classmap as $classmapItem) {
                    $path = $directory.'/'.trim($classmapItem, '/').'/';
                    $mapData = ClassMapGenerator::createMap($path, $excluded);
                    
                    foreach ($mapData as $classname => $classpath) {
                        $classmaps[$classname] = realpath($classpath);
                    }
                }
                
                Cache::put($classmapId, $classmaps, 43200);
            }
            
            $classLoader->addClassMap($classmaps);
        }
        
        if (! empty($files)) {
            foreach ($files as $file) {
                $file = $directory.'/'.ltrim($file, '/');

                if (File::exists($file)) {
                    File::requireOnce($file);
                }
            }
        }
        
        $classLoader->register();
        
        return $this;
    }
    
    /**
     * get directories.
     *
     * @param string $dirPath
     *
     * @return array
     */
    public function getDirectories($dirPath = null)
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
     * @return string|bool
     */
    public function getPathFromClass($class = null)
    {
        $reflection = new ReflectionClass(get_class($class));
        $filePath = dirname($reflection->getFileName());

        return $filePath;
    }
    
}
