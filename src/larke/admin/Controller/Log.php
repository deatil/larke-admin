<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;

use Larke\Admin\Model\AdminLog as AdminLogModel;

/**
 * 后台日志
 *
 * @create 2020-10-23
 * @author deatil
 */
class Log extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = request()->get('start', 0);
        $limit = request()->get('limit', 10);
        
        $order = request()->get('order', 'DESC');
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        $total = AdminLogModel::count(); 
        $list = AdminLogModel::offset($start)
            ->limit($limit)
            ->withCertain('admin', ['name', 'nickname', 'email', 'avatar', 'last_active', 'last_ip'])
            ->orderBy('add_time', $order)
            ->get()
            ->toArray(); 
        
        $this->successJson(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 详情
     *
     * @param  Request  $request
     * @return Response
     */
    public function detail(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            $this->errorJson(__('日志ID不能为空'));
        }
        
        $info = AdminLogModel::where(['id' => $id])
            ->withCertain('admin', ['name', 'email', 'avatar', 'last_active', 'last_ip'])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('日志信息不存在'));
        }
        
        $this->successJson(__('获取成功'), $info);
    }
    
    /**
     * 删除
     *
     * @param  Request  $request
     * @return Response
     */
    public function delete(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            $this->errorJson(__('日志ID不能为空'));
        }
        
        $info = AdminLogModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('日志信息不存在'));
        }
        
        $deleteStatus = AdminLogModel::where(['id' => $id])
            ->delete();
        if ($deleteStatus === false) {
            $this->errorJson(__('日志删除失败'));
        }
        
        $this->successJson(__('日志删除成功'));
    }
    
}