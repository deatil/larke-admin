<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Larke\Admin\Model\Extension as ExtensionModel;

/**
 * 扩展更新配置之后
 *
 * @create 2020-12-12
 * @author deatil
 */
class ExtensionConfigAfter
{
    /**
     * 扩展名称
     * 
     * @var string
     */
    public $name;
    
    /**
     * 请求数据
     * 
     * @var Larke\Admin\Model\Extension
     */
    public $extension;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param Larke\Admin\Model\Extension $extension 扩展数据
     */
    public function __construct(string $name, ExtensionModel $extension)
    {
        $this->name = $name;
        
        $this->extension = $extension;
    }
    
}
