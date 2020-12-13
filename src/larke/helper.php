<?php

use Larke\Admin\Traits\ResponseJson as ResponseJsonTrait;

if (!function_exists('larke_success')) {
    /*
     * 返回成功JSON
     *
     * @create 2020-10-19
     * @author deatil
     */
    function larke_success($message = '获取成功', $data = null, $code = 0, $header = []) {
        return (new class {
            use ResponseJsonTrait;
            
            public function json($message = '获取成功', $data = null, $code = 0, $header = [])
            {
                return $this->success($message, $data, $code, $header);
            }
        })->json($message, $data, $code, $header);
    }
}

if (!function_exists('larke_error')) {
    /*
     * 返回失败JSON
     *
     * @create 2020-10-19
     * @author deatil
     */
    function larke_error($message = '获取失败', $code = 1, $data = [], $header = []) {
        return (new class {
            use ResponseJsonTrait;
            
            public function json($message = null, $code = 1, $data = [], $header = [])
            {
                return $this->error($message, $code, $data, $header);
            }
        })->json($message, $code, $data, $header);
    }
}
