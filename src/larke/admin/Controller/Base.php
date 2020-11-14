<?php

namespace Larke\Admin\Controller;

use Carbon\Carbon;

use Larke\Admin\Http\Controller as BaseController;

class Base extends BaseController
{
    /**
     * 时间格式化到时间戳
     */
    protected function formatDate($date = '')
    {
        if (empty($date)) {
            return false;
        }
        
        return Carbon::parse($date)->timestamp;
    }
    
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
