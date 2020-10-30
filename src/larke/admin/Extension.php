<?php

namespace Larke\Admin;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Route;

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
     * Get extension info.
     *
     * @param array
     */
    public function getExtension($name = null)
    {
        if (empty($name)) {
            return [];
        }
        
        $className = Arr::get($this->extensions, $name);
        if (class_exists($className)) {
            return [];
        }
        
        $newClass = app($className);
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
            'setting' => Arr::get($info, 'setting', []),
            'class_name' => $className,
        ];
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
        if (class_exists($className)) {
            return false;
        }
        
        return app($className);
    }
    
    /**
     * Get extensions.
     *
     * @param array
     */
    public function getExtensions()
    {
        $extensions = $this->extensions;
        
        $list = collect($extensions)->map(function($className) {
            if (class_exists($className)) {
                $newClass = app($className);
                
                if (isset($newClass->info)) {
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
                        'setting' => Arr::get($info, 'setting', []),
                        'class_name' => $className,
                    ];
                }
            }
        })->filter(function($data) {
            return !empty($data);
        })->toArray();
        
        return $list;
    }
}
