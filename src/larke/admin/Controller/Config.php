<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Event;
use Larke\Admin\Model\Config as ConfigModel;

/**
 * 配置
 *
 * @title 配置
 * @desc 系统配置管理
 * @order 250
 * @auth true
 * @slug {prefixAs}config
 *
 * @create 2020-10-25
 * @author deatil
 */
class Config extends Base
{
    /**
     * 列表
     *
     * @title 配置列表
     * @desc 系统配置列表
     * @order 251
     * @auth true
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $limit = (int) $request->input('limit', 10);
        
        $order = $this->formatOrderBy($request->input('order', 'ASC'));
        
        $searchword = $request->input('searchword', '');
        $orWheres = [];
        if (! empty($searchword)) {
            $orWheres = [
                ['type', 'like', '%'.$searchword.'%'],
                ['title', 'like', '%'.$searchword.'%'],
                ['name', 'like', '%'.$searchword.'%'],
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
       
        $group = $request->input('group');
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
                return $this->error($validator->errors()->first());
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
        
        return $this->success(__('获取成功'), [
            'start' => $start,
            'limit' => $limit,
            'total' => $total,
            'list' => $list,
        ]);
    }
    
    /**
     * 详情
     *
     * @title 配置详情
     * @desc 系统配置详情
     * @order 252
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
        
        $info = ConfigModel::where('id', '=', $id)
            ->orWhere('name', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        return $this->success(__('获取成功'), $info);
    }
    
    /**
     * 删除
     *
     * @title 配置删除
     * @desc 系统配置删除
     * @order 253
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
        
        $info = ConfigModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('信息删除失败'));
        }
        
        return $this->success(__('信息删除成功'));
    }
    
    /**
     * 添加
     * type: text,textarea,number,radio,select,checkbox,array,switch,image,images
     *
     * @title 配置添加
     * @desc 系统配置添加
     * @order 254
     * @auth true
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
            return $this->error($validator->errors()->first());
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
            'is_show' => ($request->input('is_show', 0) == 1) ? 1 : 0,
            'is_system' => ($request->input('is_system', 0) == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        
        $config = ConfigModel::create($insertData);
        if ($config === false) {
            return $this->error(__('信息添加失败'));
        }
        
        // 监听事件
        event(new Event\ConfigCreated($config));
        
        return $this->success(__('信息添加成功'), [
            'id' => $config->id,
        ]);
    }
    
    /**
     * 更新
     *
     * @title 配置更新
     * @desc 系统配置更新
     * @order 255
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
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
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
            return $this->error($validator->errors()->first());
        }
        
        $nameInfo = ConfigModel::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($nameInfo)) {
            return $this->error(__('要修改成的名称已经存在'));
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
            return $this->error(__('信息修改失败'));
        }
        
        // 监听事件
        event(new Event\ConfigUpdated($info));
        
        return $this->success(__('信息修改成功'));
    }
    
    /**
     * 配置全部列表
     *
     * @title 配置全部列表
     * @desc 配置全部列表，没有分页
     * @order 256
     * @auth true
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
        
        return $this->success(__('获取成功'), [
            'list' => $list,
        ]);
        
    }
    
    /**
     * 更新配置
     *
     * @title 更新配置
     * @desc 更新配置
     * @order 257
     * @auth true
     *
     * @return Response
     */
    public function setting(Request $request)
    {
        $fields = $request->input('fields');
        
        event(new Event\ConfigSettingBefore($fields));
        
        if (!empty($fields)) {
            ConfigModel::setMany($fields);
        }
        
        event(new Event\ConfigSettingAfter($fields));
        
        return $this->success(__('设置更新成功'));
    }
    
    /**
     * 获取配置数组
     *
     * @title 获取配置数组
     * @desc 获取配置全部数组
     * @order 258
     * @auth true
     *
     * @return Response
     */
    public function settings()
    {
        $settings = ConfigModel::getSettings();
        
        event(new Event\ConfigSettingsAfter($settings));
        
        return $this->success(__('获取成功'), [
            'settings' => $settings,
        ]);
    }
    
    /**
     * 排序
     *
     * @title 配置排序
     * @desc 配置排序
     * @order 259
     * @auth true
     *
     * @param string $id
     * @param  Request  $request
     * @return Response
     */
    public function listorder(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('ID不能为空'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('信息不存在'));
        }
        
        $listorder = $request->input('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('更新排序失败'));
        }
        
        return $this->success(__('更新排序成功'));
    }
    
    /**
     * 启用
     *
     * @title 配置启用
     * @desc 配置启用
     * @order 260
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
        
        $info = ConfigModel::where('id', '=', $id)
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
     * @title 配置禁用
     * @desc 配置禁用
     * @order 261
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
        
        $info = ConfigModel::where('id', '=', $id)
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