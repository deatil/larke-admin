<?php

namespace Larke\Admin\Support;

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
     * getLoader
     */
    public function getLoader()
    {
        return $this->loader;
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
    public function add($prefix, $paths = [], $prepend = false)
    {
        if (is_array($prefix)) {
            foreach ($prefix as $key => $value) {
                $this->add($key, $value, $prepend);
            }
            
            return $this;
        }
        
        $this->loader->add($prefix, $paths, $prepend);
        return $this;
    }
    
    /**
     * psr-4
     */
    public function addPsr4($prefix, $paths = [], $prepend = false)
    {
        if (is_array($prefix)) {
            foreach ($prefix as $key => $value) {
                $this->addPsr4($key, $value, $prepend);
            }
            
            return $this;
        }
        
        $this->loader->addPsr4($prefix, $paths, $prepend);
        return $this;
    }
    
    /**
     * psr-0
     */
    public function set($prefix, $paths = [])
    {
        if (is_array($prefix)) {
            foreach ($prefix as $key => $value) {
                $this->set($key, $value);
            }
            
            return $this;
        }
        
        $this->loader->set($prefix, $paths);
        return $this;
    }
    
    /**
     * psr-4
     */
    public function setPsr4($prefix, $paths = [])
    {
        if (is_array($prefix)) {
            foreach ($prefix as $key => $value) {
                $this->setPsr4($key, $value);
            }
            
            return $this;
        }
        
        $this->loader->setPsr4($prefix, $paths);
        return $this;
    }
    
    /**
     * 注册
     */
    public function register($prepend = true)
    {
        $this->loader->register($prepend);
    }
    
}