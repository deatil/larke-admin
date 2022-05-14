<?php

declare (strict_types = 1);

namespace Larke\Admin\Permission;

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
     * @var \Casbin\Enforcer 决策器
     */
    protected $enforcer = '';
    
    /**
     * 缓存
     *
     * @var \Illuminate\Cache\Repository
     */
    protected $cache;
    
    /**
     * @var string 缓存前缀
     */
    protected $cachePrefix = 'larke-permission';
    
    /**
     * 构造函数
     *
     * @param string \Casbin\Enforcer 决策器
     * @param string \Illuminate\Cache\Repository 缓存
     */
    public function __construct($enforcer, $cache) 
    {
        // 决策器
        $this->enforcer = $enforcer;
        
        // 缓存
        $this->cache = $cache;
    }
    
    /**
     * 设置决策器
     */
    public function WithEnforcer($enforcer)
    {
        $this->enforcer = $enforcer;
        
        return $this;
    }
    
    /**
     * 获取决策器
     */
    public function getEnforcer()
    {
        return $this->enforcer;
    }
    
    /**
     * 设置缓存
     */
    public function WithCache($cache)
    {
        $this->cache = $cache;
        
        return $this;
    }
    
    /**
     * 获取缓存
     */
    public function getCache()
    {
        return $this->cache;
    }
    
    /**
     * 设置缓存前缀
     */
    public function WithCachePrefix(string $prefix)
    {
        $this->cachePrefix = $prefix;
        
        return $this;
    }
    
    /**
     * 获取缓存前缀
     */
    public function getCachePrefix()
    {
        return $this->cachePrefix;
    }
    
    /**
     * 验证用户权限
     */
    public function enforce(string $user, string $slug, string $method)
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
    public function getPermissionsForUser(string $user)
    {
        $key = $this->wrapperCacheKey($user);
        
        return $this->cache->rememberForever($key, function() use($user) {
            return $this->enforcer->getImplicitPermissionsForUser($user);
        });
    }
    
    /**
     * 删除用户缓存的全部权限
     */
    public function forgetCachePermissionsForUser(string $user)
    {
        $key = $this->wrapperCacheKey($user);
        
        return $this->cache->forget($key);
    }
    
    /**
     * 包装缓存 key 值
     */
    public function wrapperCacheKey(string $key)
    {
        return $this->cachePrefix . ':' . substr(md5($key), 8, 16);
    }
}
