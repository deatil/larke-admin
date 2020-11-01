<?php

namespace Larke\Admin;

use Composer\Autoload\ClassLoader;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Model\Extension as ExtensionModel;
use Larke\Admin\Extension\Service as ExtensionService;

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
     * @param string $name
     * @param string $class
     *
     * @return void
     */
    public function extend($name, $class)
    {
        $this->extensions[$name] = $class;
    }
    
    /**
     * Set routes for this Route.
     *
     * @param $callback
     */
    public function routes($callback, $config = [])
    {
        $attributes = array_merge(
            [
                'prefix' => config('larke.route.prefix'),
                'middleware' => config('larke.route.middleware'),
            ],
            $config
        );

        Route::group($attributes, $callback);
    }
    
    /**
     * Register extensions'namespace.
     *
     * @param array
     */
    public function registerExtensionNamespace()
    {
        $dir = $this->getExtensionDirectory();
        
        // 注入扩展命名空间
        $loader = new ClassLoader();
        $useStaticLoader = PHP_VERSION_ID >= 50600 && !defined('HHVM_VERSION') && (!function_exists('zend_loader_file_encoded') || !zend_loader_file_encoded());
        if ($useStaticLoader) {
            call_user_func(\Closure::bind(function () use ($loader, $dir) {
                $loader->prefixLengthsPsr4 = [];
                $loader->prefixDirsPsr4 = [];
                $loader->fallbackDirsPsr0 = [
                    0 => $dir,
                ];
                $loader->classMap = [];
            }, null, ClassLoader::class));
        } else {
            $loader->set('', $dir);
        }
        $loader->register(true);
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
        
        $services = collect($list)->map(function($data) {
            if ($data['status'] != 1) {
                return null;
            }

            if (empty($data['class_name'])) {
                return null;
            }
            
            $newClass = app($data['class_name']);
            if (!$newClass) {
                return null;
            }
            
            if (! ($newClass instanceof ExtensionService)) {
                return null;
            }
            
            return $newClass;
        })->filter(function($data) {
            return !empty($data);
        })->toArray();
        
        array_walk($services, function ($s) {
            $this->bootService($s);
        });
    }
    /**
     * Boot the given service.
     */
    protected function bootService(ExtensionService $service)
    {
        $service->callBootingCallbacks();

        if (method_exists($service, 'boot')) {
            app()->call([$service, 'boot']);
        }

        $service->callBootedCallbacks();
    }
    
    /**
     * Load extensions.
     *
     * @param $this
     */
    public function loadExtension()
    {
        $dir = $this->getExtensionDirectory();
        
        // 注入在扩展目录的扩展
        $dirs = isset($dir) ? scandir($dir) : [];
        foreach ($dirs as $value) {
            $bootstrapDir = $dir . DIRECTORY_SEPARATOR . $value;
            $bootstrap = $bootstrapDir . DIRECTORY_SEPARATOR . 'bootstrap.php';
            if ($bootstrapDir != '.' 
                && $bootstrapDir != '..'
                && file_exists($bootstrap)
            ) {
                include_once $bootstrap;
            }
        }
        
        return $this;
    }
    
    /**
     * Get extensions directory.
     *
     * @param array
     */
    public function getExtensionDirectory($path = '')
    {
        $extensionDirectory =  config('larke.extension.directory');
        return $extensionDirectory.($path ? DIRECTORY_SEPARATOR.$path : $path);
    }
    
    /**
     * Get new class.
     *
     * @param boolen|object
     */
    public function getNewClass($className = null)
    {
        if (!class_exists($className)) {
            return false;
        }
        
        $newClass = app($className);
        if (!($newClass instanceof ExtensionService)) {
            return false;
        }
        
        return $newClass;
    }
    
    /**
     * Get new class.
     *
     * @param mixed
     */
    public function getNewClassMethod($className = null, $method = null, $param = [])
    {
        if (empty($className) || empty($method)) {
            return false;
        }
        
        $newClass = $this->getNewClass($className);
        if (!$newClass) {
            return false;
        }
        
        if (!method_exists($newClass, $method)) {
            return false;
        }
        
        $res = call_user_func_array([$newClass, $method], $param);
        return $res;
    }
    
    /**
     * Get extension new class.
     *
     * @param boolen|object
     */
    public function getExtensionNewClass($name = null)
    {
        if (empty($name)) {
            return false;
        }
        
        $className = Arr::get($this->extensions, $name);
        
        return $this->getNewClass($className);
    }
    
    /**
     * Get extension info.
     *
     * @param array
     */
    public function getExtension($name = null)
    {
        $newClass = $this->getExtensionNewClass($name);
        if ($newClass === false) {
            return [];
        }
        
        if (!isset($newClass->info)) {
            return [];
        }
        
        $info = $newClass->info;
        
        return [
            'name' => Arr::get($info, 'name'),
            'title' => Arr::get($info, 'title'),
            'introduce' => Arr::get($info, 'introduce'),
            'author' => Arr::get($info, 'author'), 
            'authorsite' => Arr::get($info, 'authorsite'),
            'authoremail' => Arr::get($info, 'authoremail'),
            'version' => Arr::get($info, 'version'),
            'adaptation' => Arr::get($info, 'adaptation'),
            'need_module' => Arr::get($info, 'need_module', []),
            'config' => Arr::get($info, 'config', []),
            'class_name' => Arr::get($this->extensions, $name),
        ];
    }
    
    /**
     * Get extension config.
     *
     * @param array
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
     * @param array
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
     * @param boolen
     */
    public function validateInfo(array $info)
    {
        $mustInfo = [
            'name',
            'title',
            'introduce',
            'author',
            'version',
            'adaptation',
        ];
        if (empty($info)) {
            return false;
        }
        
        return !collect($mustInfo)
            ->contains(function ($key) use ($info) {
                return !isset($info[$key]);
            });
    }
    
    /**
     * Create rule.
     *
     * @param array
     */
    public function createRule(
        $data = [], 
        $parentId = 0, 
        array $children = []
    ) {
        if (empty($data)) {
            return false;
        }
        
        $lastOrder = AuthRuleModel::max('listorder');
        
        $rule = AuthRuleModel::create([
            'parentid' => $parentId,
            'listorder' => $lastOrder + 1,
            'title' => Arr::get($data, 'title'),
            'url' => Arr::get($data, 'url'),
            'method' => Arr::get($data, 'method'),
            'slug' => Arr::get($data, 'slug'),
            'description' => Arr::get($data, 'description'),
        ]);
        if (!empty($children)) {
            foreach ($children as $child) {
                $subChildren = Arr::get($child, 'children', []);
                $this->createRule($child, $rule->id, $subChildren);
            }
        }

        return $rule;
    }
}
