<?php

declare (strict_types = 1);

namespace Larke\Admin\Composer;

use Illuminate\Support\Facades\File;

use Larke\Admin\Composer\Resolve;

/*
 * 仓库
 *
 * @create 2021-1-25
 * @author deatil
 */
class Repository
{
    /**
     * @var string
     */
    protected $directory = '';
    
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
     * 注册仓库
     *
     * @param string $name
     * @param array $repository
     *
     * @return object
     */
    public function register(string $name, array $repository = [])
    {
        if (empty($this->directory)) {
            return false;
        }
        
        $resolve = Resolve::create()->withDirectory($this->directory);
        $composerPath = $resolve->getComposerNamePath();
        
        $contents = $resolve->registerRepository($name, $repository);
        $data = $resolve->formatToJson($contents);
        if (empty($data)) {
            return false;
        }
        
        return File::put($composerPath, $data, true);
    }
    
    /**
     * 移除仓库
     *
     * @param string $name
     *
     * @return object
     */
    public function remove(string $name)
    {
        if (empty($this->directory)) {
            return false;
        }
        
        $resolve = Resolve::create()->withDirectory($this->directory);
        $composerPath = $resolve->getComposerNamePath();
        
        $contents = $resolve->removeRepository($name);
        $data = $resolve->formatToJson($contents);
        if (empty($data)) {
            return false;
        }
        
        return File::put($composerPath, $data, true);
    }
}
