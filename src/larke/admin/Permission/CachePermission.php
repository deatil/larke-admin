<?php

declare (strict_types = 1);

namespace Larke\Admin\Permission;

use Casbin\Enforcer;
use Illuminate\Cache\Repository;

/**
 * 用户缓存权限后判断
 *
 * $enforcer = \Larke\Admin\Facade\Permission::guard('larke');
 * $cache    = \Illuminate\Support\Facades\Cache::store('file');
 *
 * $perm = new CachePermission($enforcer, $cache);
 * $res  = $perm->enforce($user, $slug, $method);
 *
 * @create 2022-5-14
 * @author deatil
 */
class CachePermission
{
    /**
     * @var Enforcer 决策器
     */
    protected Enforcer $enforcer = '';
    
    /**
     * @var Repository 缓存
     */
    protected Repository $cache;
    
    /**
     * @var string 缓存前缀
     */
    protected string $cachePrefix = 'larke-permission';
    
    /**
     * 构造函数
     *
     * @param string Enforcer 决策器
     * @param string Repository 缓存
     */
    public function __construct(Enforcer $enforcer, Repository $cache) 
    {
        // 决策器
        $this->enforcer = $enforcer;
        
        // 缓存
        $this->cache = $cache;
    }
    
    /**
     * 设置决策器
     */
    public function WithEnforcer(Enforcer $enforcer): self
    {
        $this->enforcer = $enforcer;
        
        return $this;
    }
    
    /**
     * 获取决策器
     */
    public function getEnforcer(): Enforcer
    {
        return $this->enforcer;
    }
    
    /**
     * 设置缓存
     */
    public function WithCache(Repository $cache): self
    {
        $this->cache = $cache;
        
        return $this;
    }
    
    /**
     * 获取缓存
     */
    public function getCache(): Repository
    {
        return $this->cache;
    }
    
    /**
     * 设置缓存前缀
     */
    public function WithCachePrefix(string $prefix): self
    {
        $this->cachePrefix = $prefix;
        
        return $this;
    }
    
    /**
     * 获取缓存前缀
     */
    public function getCachePrefix(): string
    {
        return $this->cachePrefix;
    }
    
    /**
     * 验证用户权限
     */
    public function enforce(string $user, string $slug, string $method): bool
    {
        $perms = $this->getPermissionsForUser($user);
        if (empty($perms)) {
            return false;
        }
        
        foreach ($perms as $perm) {
            // 满足条件
            if ($perm[1] == $slug && $perm[2] == $method) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * 用户全部缓存权限
     */
    public function getPermissionsForUser(string $user): mixed
    {
        $key = $this->wrapperCacheKey($user);
        
        return $this->cache->rememberForever($key, function() use($user) {
            return $this->enforcer->getImplicitPermissionsForUser($user);
        });
    }
    
    /**
     * 删除用户缓存的全部权限
     */
    public function forgetCachePermissionsForUser(string $user): bool
    {
        $key = $this->wrapperCacheKey($user);
        
        return $this->cache->forget($key);
    }
    
    /**
     * 包装缓存 key 值
     */
    public function wrapperCacheKey(string $key): string
    {
        $newKey = substr(md5($key), 8, 16);
        if (empty($this->cachePrefix)) {
            return $newKey;
        }
        
        return $this->cachePrefix . ':' . $newKey;
    }
}
