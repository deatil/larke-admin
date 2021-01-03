<?php

declare (strict_types = 1);

namespace Larke\Admin\Service;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

use Larke\Admin\Support\Composer as SupportComposer;
use Larke\Admin\Support\ClassMapGenerator;

class Composer
{
    /**
     * @var string
     */
    protected $directory = '';
    
    /**
     * @var string
     */
    protected $composerName = 'composer.json';
    
    /**
     * 创建
     *
     * @return object
     */
    public static function create()
    {
        return new static();
    }
    
    /**
     * 目录
     *
     * @param string $directory
     *
     * @return object
     */
    public function withDirectory($directory)
    {
        $this->directory = $directory;
        return $this;
    }
    
    /**
     * composer 文件名称
     *
     * @param string $composerName
     *
     * @return object
     */
    public function withComposerName($composerName)
    {
        $this->composerName = $composerName;
        return $this;
    }
    
    /**
     * 获取composer信息
     *
     * @return array
     */
    public function getData()
    {
        $info = $this->getInfo();
        $require = $this->getRequire();
        
        $autoloads = $this->getAutoload('autoload');
        $formatAutoloads = $this->formatAutoload($autoloads, $this->directory);
        
        $autoloadDevs = $this->getAutoload('autoload-dev');
        $formatAutoloadDevs = $this->formatAutoload($autoloadDevs, $this->directory);
        
        $providers = $this->getProviders();
        
        return [
            'info' => $info,
            'require' => $require,
            'autoload' => $formatAutoloads,
            'autoload-dev' => $formatAutoloadDevs,
            'providers' => $providers,
        ];
    }
    
    /**
     * 获取composer
     *
     * @return array
     */
    public function getComposer()
    {
        if (empty($this->directory)) {
            return [];
        }
        
        $directory = $this->directory;
        $composerFile = $directory . '/' . $this->composerName;
        $composerProperty = SupportComposer::parse($composerFile);
        
        return $composerProperty;
    }
    
    /**
     * 获取composer信息
     *
     * @return array
     */
    public function getInfo()
    {
        $composerProperty = $this->getComposer();
        
        $info = $composerProperty->toArray();
        
        $info = Arr::only($info, [
            'name', 
            'description', 
            'license', 
            'type',
            'keywords',
            'homepage',
            'authors',
        ]);
        
        return $info;
    }
    
    /**
     * 依赖
     *
     * @return array
     */
    public function getRequire()
    {
        $composerProperty = $this->getComposer();
        
        $require = $composerProperty->get('require', []);
        
        return $require;
    }
    
    /**
     * 自动加载信息
     *
     * @param string $autoload
     *
     * @return array
     */
    public function getAutoload(string $autoload = 'autoload')
    {
        $composerProperty = $this->getComposer();
        
        $psr0 = $composerProperty->get($autoload.'.psr-0');
        $psr4 = $composerProperty->get($autoload.'.psr-4');
        $classmap = $composerProperty->get($autoload.'.classmap');
        $files = $composerProperty->get($autoload.'.files');
        $exclude = $composerProperty->get($autoload.'.exclude-from-classmap');
        
        $data = [
            'psr-0' => $psr0,
            'psr-4' => $psr4,
            'classmap' => $classmap,
            'files' => $files,
            'exclude-from-classmap' => $exclude,
        ];
        
        return $data;
    }
    
    /**
     * 服务提供者
     *
     * @return array
     */
    public function getProviders()
    {
        $composerProperty = $this->getComposer();
        
        $providers = $composerProperty->get('extra.laravel.providers', []);
        
        return $providers;
    }
    
    /**
     * 格式化自动加载信息
     *
     * @param array $autoload
     * @param string $directory
     *
     * @return array
     */
    public function formatAutoload(array $autoload, string $directory)
    {
        if (empty($autoload) || empty($directory)) {
            return [];
        }
        
        $psr0 = Arr::get($autoload, 'psr-0', []);
        $psr4 = Arr::get($autoload, 'psr-4', []);
        $classmap = Arr::get($autoload, 'classmap', []);
        $files = Arr::get($autoload, 'files', []);
        $exclude = Arr::get($autoload, 'exclude-from-classmap', []);
        
        $newPsr0 = [];
        if (! empty($psr0)) {
            foreach ($psr0 as $namespace => $path) {
                $path = $directory.'/'.trim($path, '/').'/';
                $newPsr0[$namespace] = realpath($path);
            }
        }
        
        $newPsr4 = [];
        if (! empty($psr4)) {
            foreach ($psr4 as $namespace => $path) {
                $path = $directory.'/'.trim($path, '/').'/';
                $newPsr4[$namespace] = realpath($path);
            }
        }
        
        $newClassmap = [];
        $excluded = ClassMapGenerator::excluded($exclude);
        if (! empty($classmap)) {
            foreach ($classmap as $classmapItem) {
                $path = $directory.'/'.trim($classmapItem, '/').'/';
                $mapData = ClassMapGenerator::createMap($path, $excluded);
                
                foreach ($mapData as $classname => $classpath) {
                    $newClassmap[$classname] = realpath($classpath);
                }
            }
        }
        
        $newFiles = [];
        if (! empty($files)) {
            foreach ($files as $file) {
                $file = $directory.'/'.ltrim($file, '/');
                $newFiles[] = realpath($file);
            }
        }
        
        $data = [
            'psr-0' => $newPsr0,
            'psr-4' => $newPsr4,
            'classmap' => $newClassmap,
            'files' => $newFiles,
        ];
        
        return $data;
    }

    /**
     * 注册自动加载
     *
     * @param array $autoload
     *
     * @return object
     */
    public function registerAutoload(array $autoload = [])
    {
        if (empty($autoload)) {
            return $this;
        }
        
        $psr0 = Arr::get($autoload, 'psr-0', []);
        $psr4 = Arr::get($autoload, 'psr-4', []);
        $classmap = Arr::get($autoload, 'classmap', []);
        $files = Arr::get($autoload, 'files', []);

        $classLoader = app('larke.admin.loader');
        
        if (! empty($psr0)) {
            foreach ($psr0 as $namespace0 => $path0) {
                $classLoader->add($namespace0, $path0);
            }
        }
        
        if (! empty($psr4)) {
            foreach ($psr4 as $namespace => $path) {
                $classLoader->addPsr4($namespace, $path);
            }
        }
        
        if (! empty($classmap) && is_array($classmap)) {
            $classLoader->addClassMap($classmap);
        }
        
        if (! empty($files)) {
            foreach ($files as $file) {
                if (File::exists($file)) {
                    File::requireOnce($file);
                }
            }
        }
        
        $classLoader->register();
        
        return $this;
    }
    
    /**
     * 注册服务提供者
     *
     * @param array|string $provider
     *
     * @return object
     */
    public function registerProvider($provider)
    {
        if (is_array($provider)) {
            $newClasses = [];
            foreach ($provider as $p) {
                $newClasses[] = $this->registerProvider($p);
            }
            
            return $newClasses;
        }
        
        if (! class_exists($provider)) {
            return null;
        }
        
        $newClass = app()->register($provider);
        return $newClass;
    }
}
