<?php

declare (strict_types = 1);

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
    protected ClassLoader $loader;
    
    public function __construct()
    {
        $this->loader = new ClassLoader();
    }
    
    /**
     * getLoader
     */
    public function getLoader(): ClassLoader
    {
        return $this->loader;
    }
    
    /**
     * class_map
     */
    public function addClassMap(array $classMap): self
    {
        $this->loader->addClassMap($classMap);
        
        return $this;
    }
    
    /**
     * psr-0
     */
    public function add(mixed $prefix, mixed $paths = [], bool $prepend = false): self
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
    public function addPsr4(mixed $prefix, mixed $paths = [], bool $prepend = false): self
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
    public function set(string $prefix, mixed $paths = []): self
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
    public function setPsr4(mixed $prefix, mixed $paths = []): self
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
    public function register(bool $prepend = true)
    {
        $this->loader->register($prepend);
    }
    
}