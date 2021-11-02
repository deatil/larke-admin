<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

/*
 * 网站获取配置
 *
 * @create 2020-12-11
 * @author deatil
 */
class ConfigSettingsAfter
{
    /**
     * 配置数组
     * 
     * @var Array
     */
    public $settings;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param Array $settings 网站配置
     */
    public function __construct(array $settings)
    {
        $this->settings = $settings;
    }
    
}
