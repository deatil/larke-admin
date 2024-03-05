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
     * 返回成功 json
     */
    protected function success(
        string $message, 
        mixed  $data = [], 
        array  $headers = [],
        int    $code = 0
    ): mixed {
        return Response::json(true, $code, $message, $data, $headers);
    }
    
    /**
     * 返回错误 json
     */
    protected function error(
        string $message, 
        int    $code = 1, 
        mixed  $data = [], 
        array  $headers = []
    ): mixed {
        return Response::json(false, $code, $message, $data, $headers);
    }
    
    /**
     * 将数组以标准 json 格式返回
     */
    protected function returnJson(array $data, $header = []): mixed
    {
        return Response::returnJson($data, $header);
    }
    
    /**
     * 将 json 字符窜以标准 json 格式返回
     */
    protected function returnJsonFromString($contents, $header = []): mixed
    {
        return Response::returnJsonFromString($contents, $header);
    }
    
    /**
     * 返回字符
     */
    protected function returnString($contents, $header = []): mixed
    {
        return Response::returnString($contents, $header);
    }
    
}
