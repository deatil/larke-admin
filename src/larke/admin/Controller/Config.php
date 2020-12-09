<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Model\Config as ConfigModel;
use Larke\Admin\Event\ConfigCreated as ConfigCreatedEvent;
use Larke\Admin\Event\ConfigUpdated as ConfigUpdatedEvent;

/**
 * 配置
 *
 * @title 配置
 * @desc 系统配置管理
 * @order 104
 * @auth true
 *
 * @create 2020-10-25
 * @author deatil
 */
class Config extends Base
{
    /**
     * 列表
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
                ['type', 'like', '%'.$searchword.'%'],
                ['title', 'like', '%'.$searchword.'%'],
                ['name', 'like', '%'.$searchword.'%'],
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
       
        $group = $request->get('group');
        if (!empty($group)) {
            $validator = Validator::make([
                'group' => $group,
            ], [
                'group' => 'required|alpha_num',
            ], [
                'group.required' => __('分组不能为空'),
                'group.alpha_num' => __('分组格式错误'),
            ]);
            
            if ($validator->fails()) {
                return $this->errorJson($validator->errors()->first());
            }
            
            $wheres[] = ['group', $group];
        }
        
        $query = ConfigModel::orWheres($orWheres)
            ->wheres($wheres);
        
        $total = $query->count(); 
        $list = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy('listorder', $order)
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
     * 详情
     *
     * @param string $id
     * @return Response
     */
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->orWhere('name', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        return $this->successJson(__('获取成功'), $info);
    }
    
    /**
     * 删除
     *
     * @param string $id
     * @return Response
     */
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = ConfigModel::where(['id' => $id])
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
     * type: text,textarea,number,radio,select,checkbox,array,switch,image,images
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'group' => 'required|alpha_num',
            'type' => 'required',
            'title' => 'required|max:80',
            'name' => 'required|max:30|unique:'.ConfigModel::class,
            'status' => 'required',
        ], [
            'group.required' => __('分组不能为空'),
            'group.alpha_num' => __('分组格式错误'),
            'type.required' => __('类型不能为空'),
            'title.required' => __('标题不能为空'),
            'name.required' => __('名称不能为空'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $insertData = [
            'group' => $data['group'],
            'type' => $data['type'],
            'title' => $data['title'],
            'name' => $data['name'],
            'options' => $data['options'] ?? '',
            'value' => $data['value'] ?? '',
            'description' => $data['description'],
            'listorder' => $data['listorder'] ?? 100,
            'is_show' => ($request->get('is_show', 0) == 1) ? 1 : 0,
            'is_system' => ($request->get('is_system', 0) == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        
        $config = ConfigModel::create($insertData);
        if ($config === false) {
            return $this->errorJson(__('信息添加失败'));
        }
        
        // 监听事件
        event(new ConfigCreatedEvent($config));
        
        return $this->successJson(__('信息添加成功'), [
            'id' => $config->id,
        ]);
    }
    
    /**
     * 更新
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'group' => 'required|alpha_num',
            'type' => 'required',
            'title' => 'required|max:80',
            'name' => 'required|max:30',
            'status' => 'required',
        ], [
            'group.required' => __('分组不能为空'),
            'group.alpha_num' => __('分组格式错误'),
            'type.required' => __('类型不能为空'),
            'title.required' => __('标题不能为空'),
            'name.required' => __('名称不能为空'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $nameInfo = ConfigModel::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($nameInfo)) {
            return $this->errorJson(__('要修改成的名称已经存在'));
        }
        
        $updateData = [
            'group' => $data['group'],
            'type' => $data['type'],
            'title' => $data['title'],
            'name' => $data['name'],
            'options' => $data['options'] ?? '',
            'value' => $data['value'] ?? '',
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_show' => (isset($data['is_show']) && $data['is_show'] == 1) ? 1 : 0,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        
        // 更新信息
        $status = $info->update($updateData);
        if ($status === false) {
            return $this->errorJson(__('信息修改失败'));
        }
        
        // 监听事件
        event(new ConfigUpdatedEvent($info));
        
        return $this->successJson(__('信息修改成功'));
    }
    
    /**
     * 列表
     *
     * @return Response
     */
    public function lists()
    {
        $list = ConfigModel::where('status', '=', 1)
            ->orderBy('listorder', 'ASC')
            ->orderBy('create_time', 'ASC')
            ->select([
                'group', 
                'type',
                'title',
                'name',
                'options',
                'value',
                'description',
                'is_show',
                'listorder as sort',
            ])
            ->get()
            ->toArray(); 
        
        return $this->successJson(__('获取成功'), [
            'list' => $list,
        ]);
        
    }
    
    /**
     * 配置设置
     */
    public function setting(Request $request)
    {
        $fields = $request->get('fields');
        
        if (!empty($fields)) {
            ConfigModel::setMany($fields);
        }
        
        return $this->successJson(__('设置更新成功'));
    }
    
    /**
     * 配置数组
     */
    public function settings()
    {
        $settings = ConfigModel::getSettings();
        
        return $this->successJson(__('获取成功'), [
            'settings' => $settings,
        ]);
    }
    
    /**
     * 排序
     *
     * @param string $id
     * @param  Request  $request
     * @return Response
     */
    public function listorder(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        $listorder = $request->get('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->errorJson(__('更新排序失败'));
        }
        
        return $this->successJson(__('更新排序成功'));
    }
    
    /**
     * 启用
     *
     * @param string $id
     * @return Response
     */
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
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
     * @param string $id
     * @return Response
     */
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
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