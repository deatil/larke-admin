<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Model\AuthGroup as AuthGroupModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;

/**
 * AuthGroup
 *
 * @create 2020-10-25
 * @author deatil
 */
class AuthGroup extends Base
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
        
        $order = request()->get('order', 'desc');
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        $total = AuthGroupModel::count(); 
        $list = AuthGroupModel::offset($start)
            ->limit($limit)
            ->orderBy('create_time', $order)
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
            $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->with('ruleAccesses')
            ->first();
        if (empty($info)) {
            $this->errorJson(__('信息不存在'));
        }
        
        $ruleAccesses = collect($info['ruleAccesses'])->map(function($data) {
            return $data['rule_id'];
        });
        unset($info['ruleAccesses']);
        $info['rule_accesses'] = $ruleAccesses;
        
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
            $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('信息不存在'));
        }
        
        $deleteStatus = AuthGroupModel::where(['id' => $id])
            ->delete();
        if ($deleteStatus === false) {
            $this->errorJson(__('信息删除失败'));
        }
        
        $this->successJson(__('信息删除成功'));
    }
    
    /**
     * 添加
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        $data = request()->all();
        
        $validator = Validator::make($data, [
            'parentid' => 'required',
            'title' => 'required|max:50',
            'status' => 'required',
        ], [
            'parentid.required' => __('父级分类不能为空'),
            'title.required' => __('名称不能为空'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            $this->errorJson($validator->errors()->first());
        }
        
        $id = md5(mt_rand(100000, 999999).microtime());
        $insertData = [
            'id' => $id,
            'parentid' => $data['parentid'],
            'title' => $data['title'],
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'is_root' => (isset($data['is_root']) && $data['is_root'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
            'update_time' => time(),
            'update_ip' => request()->ip(),
            'create_time' => time(),
            'create_ip' => request()->ip(),
        ];
        if (!empty($data['avatar'])) {
            $insertData['avatar'] = $data['avatar'];
        }
        
        $status = AuthGroupModel::insert($insertData);
        if ($status === false) {
            $this->errorJson(__('信息添加失败'));
        }
        
        if (isset($data['access'])) {
            $accessData = [];
            $accessArr = explode(',', $data['access']);
            foreach ($accessArr as $access) {
                $accessData[] = [
                    'rule_id' => $access,
                    'group_id' => $id,
                ];
            }
            AuthRuleAccessModel::insert($accessData);
        }
        
        $this->successJson(__('信息添加成功'), [
            'id' => $id,
        ]);
    }
    
    /**
     * 更新
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            $this->errorJson(__('信息不存在'));
        }
        
        $data = request()->all();
        $validator = Validator::make($data, [
            'parentid' => 'required',
            'title' => 'required|max:20',
            'status' => 'required',
        ], [
            'parentid.required' => __('父级分类不能为空'),
            'title.required' => __('名称不能为空'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            $this->errorJson($validator->errors()->first());
        }
        
        $updateData = [
            'parentid' => $data['parentid'],
            'title' => $data['title'],
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'is_root' => (isset($data['is_root']) && $data['is_root'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
            'update_time' => time(),
            'update_ip' => request()->ip(),
        ];
        
        // 更新信息
        $status = AuthGroupModel::where('id', $id)
            ->update($updateData);
        if ($status === false) {
            $this->errorJson(__('信息修改失败'));
        }
        
        if (isset($data['access'])) {
            AuthRuleAccessModel::where(['group_id' => $id])->delete();
            
            $accessData = [];
            $accessArr = explode(',', $data['access']);
            foreach ($accessArr as $access) {
                $accessData[] = [
                    'rule_id' => $access,
                    'group_id' => $id,
                ];
            }
            AuthRuleAccessModel::insert($accessData);
        }
        
        $this->successJson(__('信息修改成功'));
        
    }
    
}