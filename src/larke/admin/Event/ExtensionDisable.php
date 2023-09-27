<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

/**
 * 扩展禁用
 *
 * @create 2022-1-18
 * @author deatil
 */
class ExtensionDisable
{
    /**
     * 扩展名称
     * 
     * @var string
     */
    public string $name;
    
    /**
     * 扩展信息
     * 
     * @var array
     */
    public array $info;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param  string   $name 
     * @param  array    $info 
     */
    public function __construct(string $name, array $info)
    {
        $this->name = $name;
        $this->info = $info;
    }
}
