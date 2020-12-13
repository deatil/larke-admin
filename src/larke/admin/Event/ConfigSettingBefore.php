<?php

namespace Larke\Admin\Event;

/*
 * 更新配置之前
 *
 * @create 2020-12-11
 * @author deatil
 */
class ConfigSettingBefore
{
    /**
     * 请求数据
     * @var Array
     */
    public $fields;
    
    /**
     * 构造方法
     * @access public
     * @param Array $fields 请求数据
     */
    public function __construct(array $fields)
    {
        $this->fields = $fields;
    }
    
}
