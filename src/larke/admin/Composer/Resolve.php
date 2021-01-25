<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;

use Larke\Admin\Composer\Composer;
use Larke\Admin\Composer\ClassMapGenerator;

/**
 * Resolve
 *
 * @create 2021-1-10
 * @author deatil
 */
class Resolve
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
     * 获取文件路径
     *
     * @return string
     */
    public function getComposerNamePath()
    {
        return $this->directory . '/' . $this->composerName;
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
        $requireDev = $this->getRequireDev();
        
        $formatAutoload = $this->getFormatAutoload();
        $formatAutoloadDev = $this->getFormatAutoloadDev();
        
        $providers = $this->getProviders();
        $aliases = $this->getAliases();
        
        return [
            'info' => $info,
            'require' => $require,
            'require-dev' => $requireDev,
            'autoload' => $formatAutoload,
            'autoload-dev' => $formatAutoloadDev,
            'providers' => $providers,
            'aliases' => $aliases,
        ];
    }
    
    /**
     * 获取composer
     *
     * @return array
     */
    public function getComposer()
    {
        $composerFile = $this->getComposerNamePath();
        $composerProperty = Composer::parse($composerFile);
        
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
            'keywords',
            'homepage',
            'type',
            'license', 
            'authors',
            'support',
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
     * 依赖Dev
     *
     * @return array
     */
    public function getRequireDev()
    {
        $composerProperty = $this->getComposer();
        
        $require = $composerProperty->get('require-dev', []);
        
        return $require;
    }    
    
    /**
     * 获取格式化后的自动加载
     *
     * @return array
     */
    public function getFormatAutoload()
    {
        $autoloads = $this->getAutoload('autoload');
        $data = $this->formatAutoload($autoloads, $this->directory);
        
        return $data;
    }
    
    /**
     * 获取格式化后的自动加载dev
     *
     * @return array
     */
    public function getFormatAutoloadDev()
    {
        $autoloadDevs = $this->getAutoload('autoload-dev');
        $data = $this->formatAutoload($autoloadDevs, $this->directory);
        
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
     * 别名
     *
     * @return array
     */
    public function getAliases()
    {
        $composerProperty = $this->getComposer();
        
        $aliases = $composerProperty->get('extra.laravel.aliases', []);
        
        return $aliases;
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
                if (is_array($path)) {
                    foreach ($path as $pathItem) {
                        $pathItem = $directory.'/'.trim($pathItem, '/').'/';
                        $newPsr0[$namespace] = realpath($pathItem);
                    }
                } else {
                    $path = $directory.'/'.trim($path, '/').'/';
                    $newPsr0[$namespace] = realpath($path);
                }
            }
        }
        
        $newPsr4 = [];
        if (! empty($psr4)) {
            foreach ($psr4 as $namespace => $path) {
                if (is_array($path)) {
                    foreach ($path as $pathItem) {
                        $pathItem = $directory.'/'.trim($pathItem, '/').'/';
                        $newPsr4[$namespace] = realpath($pathItem);
                    }
                } else {
                    $path = $directory.'/'.trim($path, '/').'/';
                    $newPsr4[$namespace] = realpath($path);
                }
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
        if (! empty($files) && is_array($files)) {
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
    
    /**
     * 注册别名
     *
     * @param array|string $alias
     *
     * @return object
     */
    public function registerAlias($alias, $class = null)
    {
        if (is_array($alias)) {
            foreach ($alias as $name => $class) {
                $this->registerAlias($name, $class);
            }
            
            return $this;
        }
        
        class_alias($class, $alias);
        
        return $this;
    }
    
    /**
     * 注册仓库
     *
     * @param string $name
     * @param array $repository
     *
     * @return array
     */
    public function registerRepository(string $name, array $repository = [])
    {
        $this->removeRepository($name);
        
        $composerProperty = $this->getComposer();
        $data = $composerProperty->set('repositories.'.$name, $repository);
        
        return $data->toArray();
    }
    
    /**
     * 移除仓库
     *
     * @param string $name
     *
     * @return array
     */
    public function removeRepository(string $name)
    {
        $composerProperty = $this->getComposer();
        $data = $composerProperty->delete('repositories.'.$name);
        
        return $data->toArray();
    }
    
    /**
     * 格式化为json
     *
     * @param array $contents
     *
     * @return string
     */
    public function formatToJson(array $contents)
    {
        $data = json_encode($contents, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        $data = str_replace(': null', ': ""', $data);
        
        return $data;
    }

}
