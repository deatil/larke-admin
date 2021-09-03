<?php

declare (strict_types = 1);

namespace Larke\Admin\Traits;

use Larke\Admin\Facade\Response;

/**
 * 返回响应Json
 *
 * @create 2020-10-19
 * @author deatil
 */
trait ResponseJson
{
    /**
     * 返回成功json
     */
    protected function success(
        $message = null, 
        $data = null, 
        $header = [],
        $code = 0
    ) {
        return Response::json(true, $code, $message, $data, $header);
    }
    
    /**
     * 返回错误json
     */
    protected function error(
        $message = null, 
        $code = 1, 
        $data = [], 
        $header = []
    ) {
        return Response::json(false, $code, $message, $data, $header);
    }
    
    /**
     * 将json字符窜以标准json格式返回
     */
    protected function returnJsonFromString($contents, $header = [])
    {
        return Response::returnJsonFromString($contents, $header);
    }
    
    /**
     * 返回字符
     */
    protected function returnString($contents, $header = [])
    {
        return Response::returnString($contents, $header);
    }
    
}
