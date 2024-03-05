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
     *
     * @return string|null $name 
     * @return mixed
     */
    protected function switchStatus(?string $name = null): mixed
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
     *
     * @return string|null $date 
     * @return mixed
     */
    protected function formatDate(?string $date = null): mixed
    {
        if (empty($date)) {
            return false;
        }
        
        return Carbon::parse($date)->timestamp;
    }
    
    /**
     * 格式化排序
     *
     * @return string $order 
     * @return string $default 
     * @return array
     */
    protected function formatOrderBy(string $order = '', string $default = 'create_time__ASC'): array
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
