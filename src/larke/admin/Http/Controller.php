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
    protected function formatOrderBy($order = '', $default = 'create_time__ASC')
    {
        if (empty($order)) {
            $order = $default;
        }
        
        $orders = explode("__", $order);
        if (count($orders) < 2) {
            $orders = ["create_time", "ASC"];
        }
        
        $orders[1] = strtoupper($orders[1]);
        if (! in_array($orders[1], ['ASC', 'DESC'])) {
            $orders[1] = 'ASC';
        }
        
        return $orders;
    }
    
}
