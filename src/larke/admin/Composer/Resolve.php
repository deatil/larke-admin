<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Composer\Autoload\ClassLoader;

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
    protected string $directory = '';
    
    /**
     * @var string
     */
    protected string $composerName = 'composer.json';
    
    /**
     * 创建
     */
    public static function create(): self
    {
        return new self();
    }
    
    /**
     * 目录
     */
    public function withDirectory(string $directory): self
    {
        $this->directory = $directory;
        
        return $this;
    }
    
    /**
     * composer 文件名称
     */
    public function withComposerName(string $composerName): self
    {
        $this->composerName = $composerName;
        
        return $this;
    }
    
    /**
     * 获取文件路径
     */
    public function getComposerNamePath(): string
    {
        return $this->directory . '/' . $this->composerName;
    }
    
    /**
     * 获取composer信息
     */
    public function getData(): array
    {
        $info = $this->getInfo();
        $require = $this->getRequire();
        $requireDev = $this->getRequireDev();
        
        $formatAutoload = $this->getFormatAutoload();
        $formatAutoloadDev = $this->getFormatAutoloadDev();
        
        $providers = $this->getProviders();
        $aliases = $this->getAliases();
        
        return [
            'info'         => $info,
            'require'      => $require,
            'require-dev'  => $requireDev,
            'autoload'     => $formatAutoload,
            'autoload-dev' => $formatAutoloadDev,
            'providers'    => $providers,
            'aliases'      => $aliases,
        ];
    }
    
    /**
     * 获取 composer
     */
    public function getComposer(): ComposerProperty
    {
        $composerFile = $this->getComposerNamePath();
        $composerProperty = Composer::parse($composerFile);
        
        return $composerProperty;
    }
    
    /**
     * 获取 composer 信息
     */
    public function getInfo(): array
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
     */
    public function getRequire(): array
    {
        $composerProperty = $this->getComposer();
        
        $require = $composerProperty->get('require', []);
        
        return $require;
    }
    
    /**
     * 依赖 Dev
     */
    public function getRequireDev(): array
    {
        $composerProperty = $this->getComposer();
        
        $require = $composerProperty->get('require-dev', []);
        
        return $require;
    }    
    
    /**
     * 获取格式化后的自动加载
     */
    public function getFormatAutoload(): array
    {
        $autoloads = $this->getAutoload('autoload');
        $data = $this->formatAutoload($autoloads, $this->directory);
        
        return $data;
    }
    
    /**
     * 获取格式化后的自动加载dev
     */
    public function getFormatAutoloadDev(): array
    {
        $autoloadDevs = $this->getAutoload('autoload-dev');
        $data = $this->formatAutoload($autoloadDevs, $this->directory);
        
        return $data;
    }
    
    /**
     * 服务提供者
     */
    public function getProviders(): array
    {
        $composerProperty = $this->getComposer();
        
        $providers = $composerProperty->get('extra.laravel.providers', []);
        
        return $providers;
    }
    
    /**
     * 别名
     */
    public function getAliases(): array
    {
        $composerProperty = $this->getComposer();
        
        $aliases = $composerProperty->get('extra.laravel.aliases', []);
        
        return $aliases;
    }
    
    /**
     * 自动加载信息
     */
    public function getAutoload(string $autoload = 'autoload'): array
    {
        $composerProperty = $this->getComposer();
        
        $psr0     = $composerProperty->get($autoload.'.psr-0');
        $psr4     = $composerProperty->get($autoload.'.psr-4');
        $classmap = $composerProperty->get($autoload.'.classmap');
        $files    = $composerProperty->get($autoload.'.files');
        $exclude  = $composerProperty->get($autoload.'.exclude-from-classmap');
        
        $data = [
            'psr-0'    => $psr0,
            'psr-4'    => $psr4,
            'classmap' => $classmap,
            'files'    => $files,
            'exclude-from-classmap' => $exclude,
        ];
        
        return $data;
    }
    
    /**
     * 格式化自动加载信息
     */
    public function formatAutoload(array $autoload, string $directory): array
    {
        if (empty($autoload) || empty($directory)) {
            return [];
        }
        
        $psr0     = Arr::get($autoload, 'psr-0', []);
        $psr4     = Arr::get($autoload, 'psr-4', []);
        $classmap = Arr::get($autoload, 'classmap', []);
        $files    = Arr::get($autoload, 'files', []);
        $exclude  = Arr::get($autoload, 'exclude-from-classmap', []);
        
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
            'psr-0'    => $newPsr0,
            'psr-4'    => $newPsr4,
            'classmap' => $newClassmap,
            'files'    => $newFiles,
        ];
        
        return $data;
    }

    /**
     * 注册自动加载
     */
    public function registerAutoload(array $autoload = []): self
    {
        if (empty($autoload)) {
            return $this;
        }
        
        $psr0     = Arr::get($autoload, 'psr-0', []);
        $psr4     = Arr::get($autoload, 'psr-4', []);
        $classmap = Arr::get($autoload, 'classmap', []);
        $files    = Arr::get($autoload, 'files', []);

        $classLoader = new ClassLoader();
        
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
     */
    public function registerProvider(mixed $provider): mixed
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
     */
    public function registerAlias(mixed $alias, string $class = null): self
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
     * 判断是否在仓库
     */
    public function hasRepository(string $name): bool
    {
        $composerProperty = $this->getComposer();
        
        return $composerProperty->has('repositories.'.$name);
    }
    
    /**
     * 注册仓库
     */
    public function registerRepository(string $name, array $repository = []): array
    {
        $this->removeRepository($name);
        
        $composerProperty = $this->getComposer();
        $data = $composerProperty->set('repositories.'.$name, $repository);
        
        return $data->toArray();
    }
    
    /**
     * 移除仓库
     */
    public function removeRepository(string $name): array
    {
        $composerProperty = $this->getComposer();
        $data = $composerProperty->delete('repositories.'.$name);
        
        return $data->toArray();
    }
    
    /**
     * 格式化为 json
     */
    public function formatToJson(array $contents): string
    {
        $data = json_encode($contents, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        $data = str_replace(': null', ': ""', $data);
        
        return $data;
    }

}
