<?php

namespace Larke\Admin\Traits;

/*
 * Json 返回
 *
 * @create 2020-10-19
 * @author deatil
 */
trait Json
{
    /*
     * 返回成功json
     */
    protected function successJson($message = '获取成功', $data = null, $code = 0) 
    {
        return app('larke.json')->json(true, $code, $message, $data);
    }
    
    /*
     * 返回错误json
     */
    protected function errorJson($message = null, $code = 1, $data = []) 
    {
        return app('larke.json')->json(false, $code, $message, $data);
    }
    
}
