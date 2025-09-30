<?php

declare (strict_types = 1);

namespace Larke\Admin\Service;

use Illuminate\Support\Facades\Cache as LaravelCache;

/**
 * 缓存
 *
 * @create 2020-10-27
 * @author deatil
 */
class Cache
{
    /**
     * a cache store.
     *
     * @var \Illuminate\Cache\Repository
     */
    protected $store;
    
    /**
     * Cache store.
     */
    public function store(?mixed $store = null)
    {
        if (!$store) {
            $store = config('larkeadmin.cache.store', 'default');
            $store = ('default' == $store) ? null : $store;
        }
        $this->store = LaravelCache::store($store);
        
        return $this->store;
    }
}
