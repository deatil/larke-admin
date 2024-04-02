<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Tree;
use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Repository\AuthRule as AuthRuleRepository;

/**
 * 权限
 *
 * @create 2020-10-24
 * @author deatil
 */
#[RouteRule(
    title: "权限", 
    desc:  "系统权限管理",
    order: 400,
    auth:  true,
    slug:  "{prefix}auth-rule"
)]
class AuthRule extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "权限列表", 
        desc:  "系统权限列表",
        order: 401,
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
                ['url', 'like', '%'.$searchword.'%'],
                ['method', 'like', '%'.$searchword.'%'],
                ['slug', 'like', '%'.$searchword.'%'],
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
        
        $method = $request->input('method');
        if (!empty($method)) {
            $wheres[] = ['method', $method];
        }
        
        // 查询
        $query = AuthRuleModel::wheres($wheres)
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
     * 权限树结构
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "权限树结构", 
        desc:  "权限树结构列表",
        order: 402,
        auth:  true
    )]
    public function indexTree(Request $request)
    {
        $result = AuthRuleModel::orderBy('listorder', 'ASC')
            ->orderBy('slug', 'ASC')
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
     * 权限子列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "权限子列表", 
        desc:  "权限子结构列表",
        order: 403,
        auth:  true
    )]
    public function indexChildren(Request $request)
    {
        $id = $request->input('id', 0);
        if (is_array($id)) {
            return $this->error(__('larke-admin::auth_rule.id_error'));
        }
        
        $type = $request->input('type');
        if ($type == 'list') {
            $data = AuthRuleRepository::getChildren($id);
        } else {
            $data = AuthRuleRepository::getChildrenIds($id);
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
        title: "权限详情", 
        desc:  "权限详情",
        order: 404,
        auth:  true
    )]
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::auth_rule.id_dont_empty'));
        }
        
        $info = AuthRuleModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::auth_rule.info_not_exists'));
        }
        
        return $this->success(__('larke-admin::common.get_success'), $info);
    }
    
    /**
     * 删除
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "权限删除", 
        desc:  "权限删除",
        order: 405,
        auth:  true
    )]
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::auth_rule.id_dont_empty'));
        }
        
        $info = AuthRuleModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::auth_rule.info_not_exists'));
        }
        
        $childInfo = AuthRuleModel::where(['parentid' => $id])
            ->first();
        if (!empty($childInfo)) {
            return $this->error(__('larke-admin::auth_rule.rule_dont_delete'));
        }
        
        if ($info->is_system == 1) {
            return $this->error(__('larke-admin::auth_rule.system_rule_dont_delete'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('larke-admin::auth_rule.delete_fail'));
        }
        
        return $this->success(__('larke-admin::auth_rule.delete_success'));
    }
    
    /**
     * 清空特定ID权限
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "权限清空", 
        desc:  "清空特定ID权限",
        order: 406,
        auth:  true
    )]
    public function clear(Request $request)
    {
        $ids = $request->input('ids');
        if (empty($ids)) {
            return $this->error(__('larke-admin::auth_rule.rule_list_dont_empty'));
        }
        
        $ids = explode(',', $ids);
        foreach ($ids as $id) {
            $info = AuthRuleModel::where(['id' => $id])
                ->first();
            if (empty($info)) {
                continue;
            }
            
            $childInfo = AuthRuleModel::where(['parentid' => $id])
                ->first();
            if (!empty($childInfo)) {
                continue;
            }
            
            if ($info->is_system == 1) {
                continue;
            }
            
            $info->delete();
        }
        
        return $this->success(__('larke-admin::auth_rule.delete_rule_success'));
    }
    
    /**
     * 添加
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "权限添加", 
        desc:  "添加权限",
        order: 407,
        auth:  true
    )]
    public function create(Request $request)
    {
        $data = $request->all();
        
        $validator = Validator::make($data, [
            'parentid' => 'required',
            'title' => 'required|max:50',
            'url' => 'required|max:250',
            'method' => [
                'required',
                'max:10',
                Rule::in(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']),
            ],
            'slug' => 'required',
            'status' => 'required',
        ], [
            'parentid.required' => __('larke-admin::auth_rule.parent_cate_dont_empty'),
            'title.required' => __('larke-admin::auth_rule.title_dont_empty'),
            'url.required' => __('larke-admin::auth_rule.rule_dont_empty'),
            'method.required' => __('larke-admin::auth_rule.method_dont_empty'),
            'slug.required' => __('larke-admin::auth_rule.slug_dont_empty'),
            'status.required' => __('larke-admin::auth_rule.status_dont_empty'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $slugInfo = AuthRuleModel::where('slug', $data['slug'])
            ->where('method', $data['method'])
            ->first();
        if (!empty($slugInfo)) {
            return $this->error(__('larke-admin::auth_rule.slug_exists'));
        }
        
        $insertData = [
            'parentid' => $data['parentid'],
            'title' => $data['title'],
            'url' => $data['url'],
            'method' => strtoupper($data['method']),
            'slug' => $data['slug'],
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_need_auth' => (isset($data['is_need_auth']) && $data['is_need_auth'] == 1) ? 1 : 0,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        
        $rule = AuthRuleModel::create($insertData);
        if ($rule === false) {
            return $this->error(__('larke-admin::auth_rule.create_fail'));
        }
        
        return $this->success(__('larke-admin::auth_rule.create_success'), [
            'id' => $rule->id,
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
        title: "权限更新", 
        desc:  "权限更新",
        order: 408,
        auth:  true
    )]
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::auth_rule.id_dont_empty'));
        }
        
        $info = AuthRuleModel::with('children')
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::auth_rule.info_not_exists'));
        }
        
        $requestData = $request->all();
        $validator = Validator::make($requestData, [
            'parentid' => 'required',
            'title' => 'required|max:50',
            'url' => 'required|max:250',
            'method' => [
                'required',
                'max:10',
                Rule::in(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']),
            ],
            'slug' => 'required',
            'status' => 'required',
        ], [
            'parentid.required' => __('larke-admin::auth_rule.parent_cate_dont_empty'),
            'title.required' => __('larke-admin::auth_rule.title_dont_empty'),
            'url.required' => __('larke-admin::auth_rule.rule_dont_empty'),
            'method.required' => __('larke-admin::auth_rule.method_dont_empty'),
            'slug.required' => __('larke-admin::auth_rule.slug_dont_empty'),
            'status.required' => __('larke-admin::auth_rule.status_dont_empty'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $slugInfo = AuthRuleModel::where('slug', $requestData['slug'])
            ->where('method', $requestData['method'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($slugInfo)) {
            return $this->error(__('larke-admin::auth_rule.slug_exists'));
        }
        
        $childrenIds = AuthRuleRepository::getChildrenIdsFromData($info['children'], $id);
        $childrenIds[] = $id;
        if (in_array($requestData['parentid'], $childrenIds)) {
            return $this->error(__('larke-admin::auth_rule.parentid_error'));
        }
        
        $updateData = [
            'parentid' => $requestData['parentid'],
            'title' => $requestData['title'],
            'url' => $requestData['url'],
            'method' => $requestData['method'],
            'slug' => $requestData['slug'],
            'description' => $requestData['description'],
            'listorder' => $requestData['listorder'] ? intval($requestData['listorder']) : 100,
            'is_need_auth' => (isset($requestData['is_need_auth']) && $requestData['is_need_auth'] == 1) ? 1 : 0,
            'is_system' => (isset($requestData['is_system']) && $requestData['is_system'] == 1) ? 1 : 0,
            'status' => ($requestData['status'] == 1) ? 1 : 0,
        ];
        
        // 更新信息
        $status = $info->update($updateData);
        if ($status === false) {
            return $this->error(__('larke-admin::auth_rule.update_fail'));
        }
        
        return $this->success(__('larke-admin::auth_rule.update_success'));
        
    }
    
    /**
     * 排序
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "权限排序", 
        desc:  "更新权限排序",
        order: 409,
        auth:  true
    )]
    public function listorder(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::auth_rule.id_dont_empty'));
        }
        
        $info = AuthRuleModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::auth_rule.info_not_exists'));
        }
        
        $listorder = $request->input('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('larke-admin::auth_rule.sort_update_fail'));
        }
        
        return $this->success(__('larke-admin::auth_rule.sort_update_success'));
    }
    
    /**
     * 启用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "权限启用", 
        desc:  "更新权限启用",
        order: 410,
        auth:  true
    )]
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::auth_rule.id_dont_empty'));
        }
        
        $info = AuthRuleModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::auth_rule.info_not_exists'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('larke-admin::auth_rule.info_enabled'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('larke-admin::auth_rule.enable_fail'));
        }
        
        return $this->success(__('larke-admin::auth_rule.enable_success'));
    }
    
    /**
     * 禁用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "权限禁用", 
        desc:  "更新权限禁用",
        order: 411,
        auth:  true
    )]
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::auth_rule.id_dont_empty'));
        }
        
        $info = AuthRuleModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::auth_rule.info_not_exists'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('larke-admin::auth_rule.info_disabled'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('larke-admin::auth_rule.disable_fail'));
        }
        
        return $this->success(__('larke-admin::auth_rule.le.disable_success'));
    }
    
}