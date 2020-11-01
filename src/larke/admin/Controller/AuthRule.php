<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Service\Tree;
use Larke\Admin\Model\AuthRule as AuthRuleModel;
use Larke\Admin\Repository\AuthRule as AuthRuleRepository;

/**
 * AuthRule
 *
 * @create 2020-10-24
 * @author deatil
 */
class AuthRule extends Base
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
        
        $keywords = $request->get('keywords');
        
        $total = AuthRuleModel::count(); 
        $list = AuthRuleModel::offset($start)
            ->limit($limit)
            ->where('slug', 'like', '%'.$keywords.'%')
            ->orWhere('url', 'like', '%'.$keywords.'%')
            ->orderBy('slug', $order)
            ->orderBy('create_time', 'ASC')
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
        $result = AuthRuleModel::orderBy('listorder', 'ASC')
            ->orderBy('url', 'ASC')
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
        $id = $request->get('id', 0);
        if (empty($id) || is_array($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $type = $request->get('type');
        if ($type == 'list') {
            $data = AuthRuleRepository::getChildren($id);
        } else {
            $data = AuthRuleRepository::getChildrenIds($id);
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
        
        $info = AuthRuleModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
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
        
        $info = AuthRuleModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $childInfo = AuthRuleModel::where(['parentid' => $id])
            ->first();
        if (!empty($childInfo)) {
            return $this->errorJson(__('还有子权限链接存在，请删除子权限链接后再操作！'));
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
            'url' => 'required|max:250',
            'method' => [
                'required',
                'max:10',
                Rule::in(['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
            ],
            'slug' => 'required|unique:'.AuthRuleModel::class,
            'status' => 'required',
        ], [
            'parentid.required' => __('父级分类不能为空'),
            'title.required' => __('名称不能为空'),
            'url.required' => __('权限链接不能为空'),
            'method.required' => __('请求类型不能为空'),
            'slug.required' => __('链接标识不能为空'),
            'slug.unique' => __('链接标识已经存在'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
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
        if (!empty($data['avatar'])) {
            $insertData['avatar'] = $data['avatar'];
        }
        
        $createInfo = AuthRuleModel::create($insertData);
        if ($createInfo === false) {
            return $this->errorJson(__('信息添加失败'));
        }
        
        return $this->successJson(__('信息添加成功'), [
            'id' => $createInfo->id,
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
        $requestData = $request->all();
        
        $id = $request->get('id');
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AuthRuleModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $validator = Validator::make($requestData, [
            'parentid' => 'required',
            'title' => 'required|max:50',
            'url' => 'required|max:250',
            'method' => [
                'required',
                'max:10',
                Rule::in(['GET', 'POST', 'PUT', 'DELETE', 'PATCH']),
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
            return $this->errorJson($validator->errors()->first());
        }
        
        $slugInfo = AuthRuleModel::where('slug', $requestData['slug'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($slugInfo)) {
            return $this->errorJson(__('链接标识已经存在'));
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
        $status = AuthRuleModel::where('id', $id)
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
        
        $info = AuthRuleModel::where('id', '=', $id)
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
        
        $info = AuthRuleModel::where('id', '=', $id)
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
        
        $info = AuthRuleModel::where('id', '=', $id)
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
    
}