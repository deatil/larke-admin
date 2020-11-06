<?php

namespace Larke\Admin\Service;

use Composer\Autoload\ClassLoader;

/**
 * 导入
 *
 * @create 2020-11-6
 * @author deatil
 */
class Loader
{
    protected $loader;
    
    public function __construct()
    {
        $this->loader = new ClassLoader();
    }

    /**
     * class_map
     */
    public function addClassMap(array $classMap)
    {
        $this->loader->addClassMap($classMap);
        return $this;
    }
    
    /**
     * psr-0
     */
    public function add($prefix, $paths, $prepend = false)
    {
        $this->loader->add($prefix, $paths, $prepend);
        return $this;
    }
    
    /**
     * psr-4
     */
    public function addPsr4($prefix, $paths, $prepend = false)
    {
        $this->loader->addPsr4($prefix, $paths, $prepend);
        return $this;
    }
    
    /**
     * psr-0
     */
    public function set($namespace, $path)
    {
        $this->loader->set($namespace, $path);
        return $this;
    }
    
    /**
     * psr-4
     */
    public function setPsr4($namespace, $path)
    {
        $this->loader->setPsr4($namespace, $path);
        return $this;
    }
    
    /**
     * 注册
     */
    public function register()
    {
        $this->loader->register(true);
    }
    
}