<?php

namespace Larke\Admin\Controller;

use Larke\Admin\Http\Controller as BaseController;

class Base extends BaseController
{
    /**
     * 状态通用转换
     */
    protected function switchStatus($name = '')
    {
        if (empty($name)) {
            return false;
        }
        
        $statusList = [
            'open' => 1,
            'close' => 0,
        ];
        
        if (isset($statusList[$name])) {
            return $statusList[$name];
        }
        
        return false;
    }
}
