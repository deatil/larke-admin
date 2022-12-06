<?php

declare (strict_types = 1);

namespace Larke\Admin\Annotation;

use Attribute;

/**
 * 路由注解
 *
 * @create 2022-12-6
 * @author deatil
 */
#[Attribute]
class Route
{
    /**
     * 父级
     * 
     * @var string
     */
    public string $parent;
    
    /**
     * 唯一标识
     * 
     * @var string
     */
    public string $slug;
    
    /**
     * 名称
     * 
     * @var string
     */
    public string $title;
    
    /**
     * 描述
     * 
     * @var string
     */
    public string $desc;
    
    /**
     * 排序
     * 
     * @var int
     */
    public int $order;
    
    /**
     * 是否设置权限
     * 
     * @var bool
     */
    public bool $auth;
    
    /**
     * 构造方法
     */
    public function __construct(
        string $parent = "",
        string $slug = "",
        string $title = "",
        string $desc = "",
        int $order = 10000,
        bool $auth = true,
    ) {
        $this->parent = $parent;
        $this->slug   = $slug;
        $this->title  = $title;
        $this->desc   = $desc;
        $this->order  = $order;
        $this->auth   = $auth;
    }
    
    /**
     * 返回数组
     */
    public function toArray(): array
    {
        return [
            'parent' => $this->parent,
            'slug'   => $this->slug,
            'title'  => $this->title,
            'desc'   => $this->desc,
            'order'  => $this->order,
            'auth'   => $this->auth,
        ];
    }
    
}
