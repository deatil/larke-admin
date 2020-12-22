<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Tree;
use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Repository\AuthRule as AuthRuleRepository;

/**
 * 权限
 *
 * @title 权限
 * @desc 系统权限管理
 * @order 106
 * @auth true
 *
 * @create 2020-10-24
 * @author deatil
 */
class AuthRule extends Base
{
    /**
     * 列表
     *
     * @title 权限列表
     * @desc 系统权限列表
     * @order 1061
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
                ['url', 'like', '%'.$searchword.'%'],
                ['method', 'like', '%'.$searchword.'%'],
                ['slug', 'like', '%'.$searchword.'%'],
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
        
        $method = $request->get('method');
        if (!empty($method)) {
            $wheres[] = ['method', $method];
        }
        
        $query = AuthRuleModel::orWheres($orWheres)
            ->wheres($wheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy('listorder', $order)
            ->orderBy('create_time', $order)
            ->orderBy('slug', $order)
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
     * 权限树结构
     *
     * @title 权限树结构
     * @desc 权限树结构列表
     * @order 1062
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
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
        
        return $this->success(__('获取成功'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 权限子列表
     *
     * @title 权限子列表
     * @desc 权限子结构列表
     * @order 1063
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
            $data = AuthRuleRepository::getChildren($id);
        } else {
            $data = AuthRuleRepository::getChildrenIds($id);
        }
        
        return $this->success(__('获取成功'), [
            'list' => $data,
        ]);
    }
    
    /**
     * 详情
     *
     * @title 权限详情
     * @desc 权限详情
     * @order 1064
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
        
        $info = AuthRuleModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        return $this->success(__('获取成功'), $info);
    }
    
    /**
     * 删除
     *
     * @title 权限删除
     * @desc 权限删除
     * @order 1065
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
        
        $info = AuthRuleModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        $childInfo = AuthRuleModel::where(['parentid' => $id])
            ->first();
        if (!empty($childInfo)) {
            return $this->error(__('还有子权限链接存在，请删除子权限链接后再操作！'));
        }
        
        if ($info->is_system == 1) {
            return $this->error(__('系统权限不能删除'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('信息删除失败'));
        }
        
        return $this->success(__('信息删除成功'));
    }
    
    /**
     * 清空特定ID权限
     *
     * @title 权限清空
     * @desc 清空特定ID权限
     * @order 1065
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function clear(Request $request)
    {
        $ids = $request->get('ids');
        if (empty($ids)) {
            return $this->error(__('权限ID列表不能为空'));
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
        
        return $this->success(__('删除特定权限成功'));
    }
    
    /**
     * 添加
     *
     * @title 权限添加
     * @desc 添加权限
     * @order 1066
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
            'url' => 'required|max:250',
            'method' => [
                'required',
                'max:10',
                Rule::in(['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS']),
            ],
            'slug' => 'required',
            'status' => 'required',
        ], [
            'parentid.required' => __('父级分类不能为空'),
            'title.required' => __('名称不能为空'),
            'url.required' => __('权限链接不能为空'),
            'method.required' => __('请求类型不能为空'),
            'slug.required' => __('链接标识不能为空'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $slugInfo = AuthRuleModel::where('slug', $data['slug'])
            ->where('method', $data['method'])
            ->first();
        if (!empty($slugInfo)) {
            return $this->error(__('链接标识已经存在'));
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
            return $this->error(__('信息添加失败'));
        }
        
        return $this->success(__('信息添加成功'), [
            'id' => $rule->id,
        ]);
    }
    
    /**
     * 更新
     *
     * @title 权限更新
     * @desc 更新权限
     * @order 1067
     * @auth true
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('账号ID不能为空'));
        }
        
        $info = AuthRuleModel::with('children')
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
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
            'parentid.required' => __('父级分类不能为空'),
            'title.required' => __('名称不能为空'),
            'url.required' => __('权限链接不能为空'),
            'method.required' => __('请求类型不能为空'),
            'slug.required' => __('链接标识不能为空'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $slugInfo = AuthRuleModel::where('slug', $requestData['slug'])
            ->where('method', $requestData['method'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($slugInfo)) {
            return $this->error(__('链接标识已经存在'));
        }
        
        $childrenIds = AuthRuleRepository::getChildrenIdsFromData($info['children'], $id);
        $childrenIds[] = $id;
        if (in_array($requestData['parentid'], $childrenIds)) {
            return $this->error(__('父级ID设置错误'));
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
            return $this->error(__('信息修改失败'));
        }
        
        return $this->success(__('信息修改成功'));
        
    }
    
    /**
     * 排序
     *
     * @title 权限排序
     * @desc 更新权限排序
     * @order 1068
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
        
        $info = AuthRuleModel::where('id', '=', $id)
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
     * @title 权限启用
     * @desc 更新权限启用
     * @order 1069
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
        
        $info = AuthRuleModel::where('id', '=', $id)
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
     * @title 权限禁用
     * @desc 更新权限禁用
     * @order 10610
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
        
        $info = AuthRuleModel::where('id', '=', $id)
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
    
}