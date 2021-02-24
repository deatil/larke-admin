<?php

declare (strict_types = 1);

namespace Larke\Admin\Traits;

/**
 * 响应 Json 返回
 *
 * @create 2020-10-19
 * @author deatil
 */
trait ResponseJson
{
    /**
     * 返回成功json
     */
    protected function success($message = null, $data = null, $code = 0, $header = []) 
    {
        return app('larke-admin.json')->json(true, $code, $message, $data, $header);
    }
    
    /**
     * 返回错误json
     */
    protected function error($message = null, $code = 1, $data = [], $header = []) 
    {
        return app('larke-admin.json')->json(false, $code, $message, $data, $header);
    }
    
}
