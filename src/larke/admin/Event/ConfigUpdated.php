<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Larke\Admin\Model\Config as ConfigModel;

/*
 * 配置
 *
 * @create 2020-11-2
 * @author deatil
 */
class ConfigUpdated
{
    /**
     * Config 实例
     * 
     * @var \Larke\Admin\Model\Config
     */
    public $config;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param  \Larke\Admin\Model\Config  $config  配置对象
     */
    public function __construct(ConfigModel $config)
    {
        $this->config = $config;
    }
    
}
