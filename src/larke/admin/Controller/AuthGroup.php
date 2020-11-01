<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Service\Tree;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;
use Larke\Admin\Repository\AuthGroup as AuthGroupRepository;

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
        $start = $request->get('start', 0);
        $limit = $request->get('limit', 10);
        
        $order = $request->get('order', 'desc');
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
        
        return $this->successJson(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 分组列表
     *
     * @param  Request  $request
     * @return Response
     */
    public function indexTree(Request $request)
    {
        $result = AuthGroupModel::orderBy('listorder', 'ASC')
            ->orderBy('create_time', 'ASC')
            ->get()
            ->toArray(); 
        
        $Tree = new Tree();
        $list = $Tree->withData($result)->buildArray(0);
        
        return $this->successJson(__('获取成功'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 分组子列表
     *
     * @param  Request  $request
     * @return Response
     */
    public function indexChildren(Request $request)
    {
        $id = $request->get('id');
        if (empty($id) || is_array($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $type = $request->get('type');
        if ($type == 'list') {
            $data = AuthGroupRepository::getChildren($id);
        } else {
            $data = AuthGroupRepository::getChildrenIds($id);
        }
        
        return $this->successJson(__('获取成功'), $data);
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
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->with('ruleAccesses')
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $ruleAccesses = collect($info['ruleAccesses'])->map(function($data) {
            return $data['rule_id'];
        });
        unset($info['ruleAccesses']);
        $info['rule_accesses'] = $ruleAccesses;
        
        return $this->successJson(__('获取成功'), $info);
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
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->errorJson(__('信息删除失败'));
        }
        
        return $this->successJson(__('信息删除成功'));
    }
    
    /**
     * 添加
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        $data = $request->all();
        
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
            return $this->errorJson($validator->errors()->first());
        }
        
        $insertData = [
            'parentid' => $data['parentid'],
            'title' => $data['title'],
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        if (!empty($data['avatar'])) {
            $insertData['avatar'] = $data['avatar'];
        }
        
        $group = AuthGroupModel::create($insertData);
        if ($group === false) {
            return $this->errorJson(__('信息添加失败'));
        }
        
        return $this->successJson(__('信息添加成功'), [
            'id' => $group->id,
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
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $data = $request->all();
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
            return $this->errorJson($validator->errors()->first());
        }
        
        $updateData = [
            'parentid' => $data['parentid'],
            'title' => $data['title'],
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        
        // 更新信息
        $status = AuthGroupModel::where('id', $id)
            ->first()
            ->update($updateData);
        if ($status === false) {
            return $this->errorJson(__('信息修改失败'));
        }
        
        return $this->successJson(__('信息修改成功'));
    }
    
    /**
     * 排序
     *
     * @param  Request  $request
     * @return Response
     */
    public function listorder(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $listorder = $request->get('listorder', 100);
        
        $status = $info->update([
            'listorder' => intval($listorder),
        ]);
        if ($status === false) {
            return $this->errorJson(__('更新排序失败'));
        }
        
        return $this->successJson(__('更新排序成功'));
    }
    
    /**
     * 启用
     *
     * @param  Request  $request
     * @return Response
     */
    public function enable(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        if ($info->status == 1) {
            return $this->errorJson(__('信息已启用'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->errorJson(__('启用失败'));
        }
        
        return $this->successJson(__('启用成功'));
    }
    
    /**
     * 禁用
     *
     * @param  Request  $request
     * @return Response
     */
    public function disable(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        if ($info->status == 0) {
            return $this->errorJson(__('信息已禁用'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->errorJson(__('禁用失败'));
        }
        
        return $this->successJson(__('禁用成功'));
    }
    
    /**
     * 授权
     *
     * @param  Request  $request
     * @return Response
     */
    public function access(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        AuthRuleAccessModel::where([
            'group_id' => $id
        ])->get()->each->delete();
        
        $access = $request->get('access');
        if (!empty($access)) {
            $accessData = [];
            $accessArr = explode(',', $access);
            $accessArr = collect($accessArr)->unique();
            foreach ($accessArr as $value) {
                AuthRuleAccessModel::create([
                    'group_id' => $id,
                    'rule_id' => $value,
                ]);
            }
        }
        
        return $this->successJson(__('授权成功'));
    }
    
}