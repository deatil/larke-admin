<?php

declare (strict_types = 1);

namespace Larke\Admin\Event;

use Illuminate\Http\Request;

/*
 * 登陆之前
 *
 * @create 2020-11-2
 * @author deatil
 */
class PassportLoginBefore
{
    /**
     * Request 实例
     * @var \Illuminate\Http\Request
     */
    public $request;
    
    /**
     * 构造方法
     * @access public
     * @param  Illuminate\Http\Request  $data  请求数据
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }
    
}
