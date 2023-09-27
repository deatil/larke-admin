<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

/**
 * 更新配置之后
 *
 * @create 2020-12-11
 * @author deatil
 */
class ConfigSettingAfter
{
    /**
     * 请求数据
     * 
     * @var Array
     */
    public array $fields;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param Array $fields 请求数据
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }
    
}
