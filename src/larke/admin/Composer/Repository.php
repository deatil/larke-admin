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
    protected string $directory = '';
    
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
     * Resolve 对象
     */
    public function getResolve(): self
    {
        $resolve = Resolve::create()
            ->withDirectory($this->directory);
        
        return $resolve;
    }
    
    /**
     * 判断
     */
    public function has(string $name): bool
    {
        $resolve = $this->getResolve();
        
        return $resolve->hasRepository($name);
    }
    
    /**
     * 注册仓库
     */
    public function register(string $name, array $repository = []): bool
    {
        $resolve = $this->getResolve();
        
        $contents = $resolve->registerRepository($name, $repository);
        
        return $this->updateComposer($resolve, $contents);
    }
    
    /**
     * 移除仓库
     */
    public function remove(string $name): bool
    {
        $resolve = $this->getResolve();
        
        $contents = $resolve->removeRepository($name);
        
        return $this->updateComposer($resolve, $contents);
    }
    
    /**
     * 更新composer信息
     */
    public function updateComposer(Resolve $resolve, array $contents): bool
    {
        if (empty($contents)) {
            return false;
        }
        
        if (empty($this->directory)) {
            return false;
        }
        
        $composerPath = $resolve->getComposerNamePath();
        if (! File::exists($composerPath)) {
            return false;
        }
        
        try {
            $data = $resolve->formatToJson($contents);
        } catch(\Exception $e) {
            return false;
        }
        
        return File::put($composerPath, $data, true);
    }

}
