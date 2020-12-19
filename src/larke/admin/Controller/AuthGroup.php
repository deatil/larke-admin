<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Tree;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;
use Larke\Admin\Repository\AuthGroup as AuthGroupRepository;

/**
 * 管理分组
 *
 * @title 管理分组
 * @desc 系统管理分组管理
 * @order 107
 * @auth true
 *
 * @create 2020-10-25
 * @author deatil
 */
class AuthGroup extends Base
{
    /**
     * 列表
     *
     * @title 分组列表
     * @desc 系统管理分组列表
     * @order 1071
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = (int) $request->get('start', 0);
        $limit = (int) $request->get('limit', 10);
        
        $order = $this->formatOrderBy($request->get('order', 'ASC'));
        
        $searchword = $request->get('searchword', '');
        $orWheres = [];
        if (! empty($searchword)) {
            $orWheres = [
                ['title', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $this->formatDate($request->get('start_time'));
        if ($startTime !== false) {
            $wheres[] = ['create_time', '>=', $startTime];
        }
        
        $endTime = $this->formatDate($request->get('end_time'));
        if ($endTime !== false) {
            $wheres[] = ['create_time', '<=', $endTime];
        }
        
        $status = $this->switchStatus($request->get('status'));
        if ($status !== false) {
            $wheres[] = ['status', $status];
        }
        
        $query = AuthGroupModel::orWheres($orWheres)
            ->wheres($wheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy('listorder', $order)
            ->orderBy('create_time', $order)
            ->get()
            ->toArray(); 
        
        return $this->success(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 分组树结构
     *
     * @title 分组树结构
     * @desc 管理分组树结构
     * @order 1072
     * @auth true
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
        $list = $Tree
            ->withConfig('buildChildKey', 'children')
            ->withData($result)
            ->build(0);
        
        return $this->success(__('获取成功'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 分组子列表
     *
     * @title 分组子列表
     * @desc 管理分组子列表
     * @order 1073
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function indexChildren(Request $request)
    {
        $id = $request->get('id', 0);
        if (is_array($id)) {
            return $this->error(__('ID错误'));
        }
        
        $type = $request->get('type');
        if ($type == 'list') {
            $data = AuthGroupRepository::getChildren($id);
        } else {
            $data = AuthGroupRepository::getChildrenIds($id);
        }
        
        return $this->success(__('获取成功'), [
            'list' => $data,
        ]);
    }
    
    /**
     * 详情
     *
     * @title 分组详情
     * @desc 管理分组详情
     * @order 1074
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->with('ruleAccesses')
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        $ruleAccesses = collect($info['ruleAccesses'])
            ->map(function($data) {
                return $data['rule_id'];
            })
            ->values()
            ->all();
        unset($info['ruleAccesses']);
        $info['rule_accesses'] = $ruleAccesses;
        
        return $this->success(__('获取成功'), $info);
    }
    
    /**
     * 删除
     *
     * @title 分组删除
     * @desc 管理分组删除
     * @order 1075
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        if ($info->is_system == 1) {
            return $this->error(__('系统信息不能删除'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('信息删除失败'));
        }
        
        return $this->success(__('信息删除成功'));
    }
    
    /**
     * 添加
     *
     * @title 分组添加
     * @desc 管理分组添加
     * @order 1076
     * @auth true
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
            return $this->error($validator->errors()->first());
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
            return $this->error(__('信息添加失败'));
        }
        
        return $this->success(__('信息添加成功'), [
            'id' => $group->id,
        ]);
    }
    
    /**
     * 更新
     *
     * @title 分组更新
     * @desc 管理分组更新
     * @order 1077
     * @auth true
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::with('children')
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
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
            return $this->error($validator->errors()->first());
        }
        
        $childrenIds = AuthGroupRepository::getChildrenIdsFromData($info['children']);
        $childrenIds[] = $id;
        if (in_array($data['parentid'], $childrenIds)) {
            return $this->error(__('父级ID设置错误'));
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
        $status = $info->update($updateData);
        if ($status === false) {
            return $this->error(__('信息修改失败'));
        }
        
        return $this->success(__('信息修改成功'));
    }
    
    /**
     * 排序
     *
     * @title 分组排序
     * @desc 管理分组排序
     * @order 1078
     * @auth true
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function listorder(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        $listorder = $request->get('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('更新排序失败'));
        }
        
        return $this->success(__('更新排序成功'));
    }
    
    /**
     * 启用
     *
     * @title 分组启用
     * @desc 管理分组启用
     * @order 1079
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('信息已启用'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('启用失败'));
        }
        
        return $this->success(__('启用成功'));
    }
    
    /**
     * 禁用
     *
     * @title 分组禁用
     * @desc 管理分组禁用
     * @order 10710
     * @auth true
     *
     * @param string $id
     * @return Response
     */
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('信息已禁用'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('禁用失败'));
        }
        
        return $this->success(__('禁用成功'));
    }
    
    /**
     * 授权
     *
     * @title 分组授权
     * @desc 管理分组授权
     * @order 10711
     * @auth true
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function access(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
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
        
        return $this->success(__('授权成功'));
    }
    
}