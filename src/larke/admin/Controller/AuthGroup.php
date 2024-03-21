<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Tree;
use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Facade\Permission;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;
use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;
use Larke\Admin\Repository\AuthGroup as AuthGroupRepository;

/**
 * 管理分组
 *
 * @create 2020-10-25
 * @author deatil
 */
#[RouteRule(
    title: "管理分组", 
    desc:  "系统管理分组管理",
    order: 450,
    auth:  true,
    slug:  "{prefix}auth-group"
)]
class AuthGroup extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "分组列表", 
        desc:  "系统管理分组列表",
        order: 451,
        auth:  true
    )]
    public function index(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $limit = (int) $request->input('limit', 10);
        
        $order = $this->formatOrderBy($request->input('order', 'create_time__ASC'));
        
        $orWheres = [];

        $searchword = $request->input('searchword', '');
        if (! empty($searchword)) {
            $orWheres = [
                ['title', 'like', '%'.$searchword.'%'],
            ];
        }

        $wheres = [];
        
        $startTime = $this->formatDate($request->input('start_time'));
        if ($startTime !== false) {
            $wheres[] = ['create_time', '>=', $startTime];
        }
        
        $endTime = $this->formatDate($request->input('end_time'));
        if ($endTime !== false) {
            $wheres[] = ['create_time', '<=', $endTime];
        }
        
        $status = $this->switchStatus($request->input('status'));
        if ($status !== false) {
            $wheres[] = ['status', $status];
        }
        
        // 查询
        $query = AuthGroupModel::wheres($wheres)
            ->orWheres($orWheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy($order[0], $order[1])
            ->get()
            ->toArray(); 
        
        return $this->success(__('larke-admin::common.get_success'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list'  => $list,
        ]);
    }
    
    /**
     * 分组树结构
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "分组树结构", 
        desc:  "管理分组树结构",
        order: 452,
        auth:  true
    )]
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
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 分组子列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "分组子列表", 
        desc:  "管理分组子列表",
        order: 453,
        auth:  true
    )]
    public function indexChildren(Request $request)
    {
        $id = $request->input('id', 0);
        if (! is_string($id)) {
            return $this->error(__('larke-admin::common.id_error'));
        }
        
        $type = $request->input('type');
        if ($type == 'list') {
            $data = AuthGroupRepository::getChildren($id);
        } else {
            $data = AuthGroupRepository::getChildrenIds($id);
        }
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $data,
        ]);
    }
    
    /**
     * 详情
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "分组详情", 
        desc:  "管理分组详情",
        order: 454,
        auth:  true
    )]
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->with('ruleAccesses')
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        $ruleAccesses = collect($info['ruleAccesses'])
            ->map(function($data) {
                return $data['rule_id'];
            })
            ->values()
            ->all();
        unset($info['ruleAccesses']);
        $info['rule_accesses'] = $ruleAccesses;
        
        return $this->success(__('larke-admin::common.get_success'), $info);
    }
    
    /**
     * 删除
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "分组删除", 
        desc:  "管理分组删除",
        order: 455,
        auth:  true
    )]
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        $childInfo = AuthGroupModel::where(['parentid' => $id])
            ->first();
        if (!empty($childInfo)) {
            return $this->error(__('larke-admin::auth_group.group_dont_delete'));
        }
        
        if ($info->is_system == 1) {
            return $this->error(__('larke-admin::auth_group.system_info_dont_delete'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('larke-admin::common.delete_fail'));
        }
        
        return $this->success(__('larke-admin::common.delete_success'));
    }
    
    /**
     * 添加
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "分组添加", 
        desc:  "管理分组添加",
        order: 456,
        auth:  true
    )]
    public function create(Request $request)
    {
        $data = $request->all();
        
        $validator = Validator::make($data, [
            'parentid' => 'required',
            'title' => 'required|max:50',
            'status' => 'required',
        ], [
            'parentid.required' => __('larke-admin::auth_group.parent_cate_dont_empty'),
            'title.required' => __('larke-admin::auth_group.title_dont_empty'),
            'title.max' => __('larke-admin::auth_group.title_max'),
            'status.required' => __('larke-admin::auth_group.status_dont_empty'),
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
        
        $group = AuthGroupModel::create($insertData);
        if ($group === false) {
            return $this->error(__('larke-admin::auth_group.create_fail'));
        }
        
        return $this->success(__('larke-admin::auth_group.create_success'), [
            'id' => $group->id,
        ]);
    }
    
    /**
     * 更新
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "分组更新", 
        desc:  "管理分组更新",
        order: 457,
        auth:  true
    )]
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::with('children')
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'parentid' => 'required',
            'title' => 'required|max:50',
            'status' => 'required',
        ], [
            'parentid.required' => __('larke-admin::auth_group.parent_cate_dont_empty'),
            'title.required' => __('larke-admin::auth_group.title_dont_empty'),
            'title.max' => __('larke-admin::auth_group.title_max'),
            'status.required' => __('larke-admin::auth_group.status_dont_empty'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $childrenIds = AuthGroupRepository::getChildrenIdsFromData($info['children']);
        $childrenIds[] = $id;
        if (in_array($data['parentid'], $childrenIds)) {
            return $this->error(__('larke-admin::auth_group.parentid_error'));
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
            return $this->error(__('larke-admin::auth_group.update_fail'));
        }
        
        return $this->success(__('larke-admin::auth_group.update_success'));
    }
    
    /**
     * 排序
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "分组排序", 
        desc:  "管理分组排序",
        order: 458,
        auth:  true
    )]
    public function listorder(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        $listorder = $request->input('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('larke-admin::auth_group.update_sort_fail'));
        }
        
        return $this->success(__('larke-admin::auth_group.update_sort_success'));
    }
    
    /**
     * 启用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "分组启用", 
        desc:  "管理分组启用",
        order: 459,
        auth:  true
    )]
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('larke-admin::common.info_enabled'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('larke-admin::common.enable_fail'));
        }
        
        return $this->success(__('larke-admin::common.enable_success'));
    }
    
    /**
     * 禁用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "分组禁用", 
        desc:  "管理分组禁用",
        order: 459,
        auth:  true
    )]
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('larke-admin::common.info_disabled'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('larke-admin::common.disable_fail'));
        }
        
        return $this->success(__('larke-admin::common.disable_success'));
    }
    
    /**
     * 授权
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "分组授权", 
        desc:  "管理分组授权",
        order: 461,
        auth:  true
    )]
    public function access(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::common.id_dont_empty'));
        }
        
        $info = AuthGroupModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::common.info_not_exists'));
        }
        
        // 删除
        AuthRuleAccessModel::where([
            'group_id' => $id
        ])->delete();
        
        // 删除权限缓存数据
        Permission::deletePolicies($id);
        
        $access = $request->input('access');
        if (!empty($access)) {
            $accessArr = explode(',', $access);
            $accessArr = collect($accessArr)->unique();
            
            // 批量添加
            $newData = [];
            foreach ($accessArr as $value) {
                $newData[] = [
                    'id' => AuthRuleAccessModel::uuid(),
                    'group_id' => $id,
                    'rule_id' => $value,
                ];
            }
            
            AuthRuleAccessModel::insertAll($newData);
            
            // 批量赋值权限
            $policies = AuthRuleModel::whereIn("id", $accessArr)
                ->where('status', 1)
                ->select()
                ->get()
                ->mapWithKeys(function($item, $key) use($id) {
                    return [
                        $key => [$id, $item['slug'], strtoupper($item['method'])],
                    ];
                })
                ->values()
                ->all();
            Permission::addPolicies($policies);
        }
        
        return $this->success(__('larke-admin::auth_group.access_success'));
    }
    
}