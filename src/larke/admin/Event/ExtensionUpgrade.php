<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Larke\Admin\Model\Config as ConfigModel;

/**
 * 扩展更新
 *
 * @create 2022-1-18
 * @author deatil
 */
class ExtensionUpgrade
{
    /**
     * 扩展名称
     * 
     * @var string
     */
    public $name;
    
    /**
     * 扩展旧的信息
     * 
     * @var array
     */
    public $oldInfo;
    
    /**
     * 扩展新的信息
     * 
     * @var array
     */
    public $newInfo;
    
    /**
     * 构造方法
     * 
     * @access public
     * @param  string   $name 
     * @param  array    $info 
     */
    public function __construct(string $name, array $oldInfo, array $newInfo)
    {
        $this->name = $name;
        $this->oldInfo = $oldInfo;
        $this->newInfo = $newInfo;
    }
}
