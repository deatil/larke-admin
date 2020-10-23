<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;

use Larke\Admin\Model\Admin as AdminModel;

/**
 * 账号
 *
 * @create 2020-10-23
 * @author deatil
 */
class Admin extends Base
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
        
        $total = AdminModel::count(); 
        $list = AdminModel::offset($start)
            ->limit($limit)
            ->select(
                'id', 
                'name', 
                'nickname', 
                'email', 
                'avatar', 
                'status', 
                'last_active', 
                'last_ip',
                'add_time', 
                'add_ip'
            )
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
            $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AdminModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('账号信息不存在'));
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
            $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AdminModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('账号信息不存在'));
        }
        
        $deleteStatus = AdminModel::where(['id' => $id])
            ->delete();
        if ($deleteStatus === false) {
            $this->errorJson(__('账号删除失败'));
        }
        
        $this->successJson(__('账号删除成功'));
    }
    
    /**
     * 添加
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        
    }
    
    /**
     * 更新
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        
    }
    
    /**
     * 修改密码
     *
     * @param  Request  $request
     * @return Response
     */
    public function changePasssword(Request $request)
    {
        
    }
    
}