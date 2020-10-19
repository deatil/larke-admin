<?php

namespace Larke\Admin\Controller;

class Index extends Base
{
    public function index()
    {
        return $this->successJson('获取成功', [
            'msg' => 'larke-admin',
        ]);
    }
}
