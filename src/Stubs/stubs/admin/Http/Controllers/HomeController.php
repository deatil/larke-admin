<?php

declare (strict_types = 1);

namespace App\Admin\Http\Controllers;

use Illuminate\Http\Request;

use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Http\Controller as BaseController;

/**
 * Home 控制器
 *
 * @create 2022-12-09 22:55:22
 * @author lakego-admin
 */
#[RouteRule(
    title: "Home 控制器", 
    desc:  "Home 控制器",
    order: 9900,
    auth:  true,
    slug:  "app-admin.home"
)]
class HomeController extends BaseController
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title:  "数据列表", 
        desc:   "数据列表",
        order:  9901,
        parent: "app-admin.home",
        auth:   true
    )]
    public function index(Request $request)
    {
        return $this->success(__('larke-admin::common.get_success'), [
            'data' => "home controller",
        ]);
    }
}
