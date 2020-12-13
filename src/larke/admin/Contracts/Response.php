<?php

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
     * @param boolen $success
     * @param int $code
     * @param string|null $message
     * @param array|null $data
     * @return string json
     */
    public function json($success, $code, $message, $data);

}
