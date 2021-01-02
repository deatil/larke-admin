<?php

declare (strict_types = 1);

namespace Larke\Admin\Http;

use Carbon\Carbon;

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

/*
 * 基础控制器
 *
 * @create 2020-10-19
 * @author deatil
 */
abstract class Controller
{
    use ResponseJsonTrait;
    
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
     * 格式化排序
     */
    protected function formatOrderBy($order = '', $default = 'ASC')
    {
        if (empty($order)) {
            $order = $default;
        }
        
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'ASC';
        }
        
        return $order;
    }
    
}
