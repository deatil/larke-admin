<?php

declare (strict_types = 1);

namespace Larke\Admin\Contracts;

/*
 * 响应契约
 *
 * @create 2020-10-19
 * @author deatil
 */
interface Response
{
    /*
     * 响应json输出
     * 
     * @param  boolen $success
     * @param  int    $code
     * @param  string $message
     * @param  mixed  $data
     * @param  array  $header
     * @return \Illuminate\Http\Response|\Illuminate\Contracts\Routing\ResponseFactory
     */
    public function json(
        bool   $success = true, 
        int    $code = \ResponseCode::INVALID, 
        string $message = "", 
        mixed  $data = [], 
        array  $header = []
    ): mixed;

}
