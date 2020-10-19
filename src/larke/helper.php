<?php

use Larke\Admin\Traits\Json as HttpJsonTrait;

if (!function_exists('larke_success_json')) {
    /*
     * 返回成功JSON
     *
     * @create 2020-10-19
     * @author deatil
     */
    function larke_success_json($message = '获取成功', $data = null, $code = 0, $header = []) {
        return (new class {
            use HttpJsonTrait;
            
            public function json($message = '获取成功', $data = null, $code = 0, $header = [])
            {
                return $this->successJson($message, $data, $code, $header);
            }
        })->json($message, $data, $code, $header);
    }
}

if (!function_exists('larke_error_json')) {
    /*
     * 返回失败JSON
     *
     * @create 2020-10-19
     * @author deatil
     */
    function larke_error_json($message = null, $code = 1, $data = [], $header = []) {
        return (new class {
            use HttpJsonTrait;
            
            public function json($message = null, $code = 1, $data = [], $header = [])
            {
                return $this->errorJson($message, $code, $data, $header);
            }
        })->json($message, $code, $data, $header);
    }
}
