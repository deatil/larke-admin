<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Model\Config as ConfigModel;

use function Larke\Admin\do_action;
use function Larke\Admin\apply_filters;

/**
 * 配置
 *
 * @create 2020-10-25
 * @author deatil
 */
#[RouteRule(
    title: "配置", 
    desc:  "系统配置管理",
    order: 125,
    auth:  true,
    slug:  "{prefix}config"
)]
class Config extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "配置列表", 
        desc:  "系统配置列表",
        order: 100,
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
                'group.required' => __('larke-admin::config.group_dont_empty'),
                'group.alpha_num' => __('larke-admin::config.group_error'),
            ]);
            
            if ($validator->fails()) {
                return $this->error($validator->errors()->first());
            }
            
            $wheres[] = ['group', $group];
        }
        
        // 查询
        $query = ConfigModel::wheres($wheres)
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
            'list' => $list,
        ]);
    }
    
    /**
     * 详情
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "配置详情", 
        desc:  "系统配置详情",
        order: 99,
        auth:  true
    )]
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::config.id_dont_empty'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->orWhere('name', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::config.info_not_exists'));
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
        title: "配置删除", 
        desc:  "系统配置删除",
        order: 98,
        auth:  true
    )]
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::config.id_dont_empty'));
        }
        
        $info = ConfigModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::config.info_not_exists'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('larke-admin::config.delete_fail'));
        }
        
        return $this->success(__('larke-admin::config.delete_success'));
    }
    
    /**
     * 添加
     * type: text,textarea,number,radio,select,checkbox,array,switch,image,images
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "配置添加", 
        desc:  "系统配置添加",
        order: 97,
        auth:  true
    )]
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
            'group.required' => __('larke-admin::config.group_dont_empty'),
            'group.alpha_num' => __('larke-admin::config.group_error'),
            'type.required' => __('larke-admin::config.type_dont_empty'),
            'title.required' => __('larke-admin::config.title_dont_empty'),
            'name.required' => __('larke-admin::config.name_dont_empty'),
            'status.required' => __('larke-admin::config.status_dont_empty'),
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
            return $this->error(__('larke-admin::config.create_fail'));
        }
        
        // 监听事件
        do_action("config_created", $config);
        
        return $this->success(__('larke-admin::config.create_success'), [
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
    #[RouteRule(
        title: "配置更新", 
        desc:  "系统配置更新",
        order: 96,
        auth:  true
    )]
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::config.id_dont_empty'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::config.info_not_exists'));
        }
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'group' => 'required|alpha_num',
            'type' => 'required',
            'title' => 'required|max:80',
            'name' => 'required|max:30',
            'status' => 'required',
        ], [
            'group.required' => __('larke-admin::config.group_dont_empty'),
            'group.alpha_num' => __('larke-admin::config.group_error'),
            'type.required' => __('larke-admin::config.type_dont_empty'),
            'title.required' => __('larke-admin::config.title_dont_empty'),
            'name.required' => __('larke-admin::config.name_dont_empty'),
            'status.required' => __('larke-admin::config.status_dont_empty'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $nameInfo = ConfigModel::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($nameInfo)) {
            return $this->error(__('larke-admin::config.name_exists'));
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
            return $this->error(__('larke-admin::config.update_fail'));
        }
        
        // 监听事件
        do_action("config_updated", $info);
        
        return $this->success(__('larke-admin::config.update_success'));
    }
    
    /**
     * 配置全部列表
     *
     * @return Response
     */
    #[RouteRule(
        title: "配置全部列表", 
        desc:  "配置全部列表，没有分页",
        order: 95,
        auth:  true
    )]
    public function lists()
    {
        $list = ConfigModel::where('status', '=', 1)
            ->orderBy('listorder', 'DESC')
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
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
        
    }
    
    /**
     * 更新配置
     *
     * @return Response
     */
    #[RouteRule(
        title: "更新配置", 
        desc:  "更新配置",
        order: 94,
        auth:  true
    )]
    public function setting(Request $request)
    {
        $fields = $request->input('fields');
        $fields = apply_filters("config_setting_before", $fields);
        
        if (!empty($fields)) {
            ConfigModel::setMany($fields);
        }
        
        do_action("config_setting_after", $fields);
        
        return $this->success(__('larke-admin::config.setting_update_success'));
    }
    
    /**
     * 获取配置数组
     *
     * @return Response
     */
    #[RouteRule(
        title: "获取配置数组", 
        desc:  "获取配置全部数组",
        order: 93,
        auth:  true
    )]
    public function settings()
    {
        $settings = ConfigModel::getSettings();
        $settings = apply_filters("config_settings_after", $settings);
        
        return $this->success(__('larke-admin::common.get_success'), [
            'settings' => $settings,
        ]);
    }
    
    /**
     * 排序
     *
     * @param  string  $id
     * @param  Request $request
     * @return Response
     */
    #[RouteRule(
        title: "配置排序", 
        desc:  "配置排序",
        order: 92,
        auth:  true
    )]
    public function listorder(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::config.id_dont_empty'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::config.info_not_exists'));
        }
        
        $listorder = $request->input('listorder', 100);
        
        $status = $info->updateListorder($listorder);
        if ($status === false) {
            return $this->error(__('larke-admin::config.sort_update_fail'));
        }
        
        return $this->success(__('larke-admin::config.sort_update_success'));
    }
    
    /**
     * 启用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "配置启用", 
        desc:  "配置启用",
        order: 91,
        auth:  true
    )]
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::config.id_dont_empty'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::config.info_not_exists'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('larke-admin::config.info_enabled'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('larke-admin::config.enable_fail'));
        }
        
        return $this->success(__('larke-admin::config.enable_success'));
    }
    
    /**
     * 禁用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "配置禁用", 
        desc:  "配置禁用",
        order: 90,
        auth:  true
    )]
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::config.id_dont_empty'));
        }
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::config.info_not_exists'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('larke-admin::config.info_disabled'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('larke-admin::config.disable_fail'));
        }
        
        return $this->success(__('larke-admin::config.disable_success'));
    }
    
}