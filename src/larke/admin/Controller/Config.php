<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Model\Config as ConfigModel;

/**
 * Config
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
        $start = request()->get('start', 0);
        $limit = request()->get('limit', 10);
        
        $order = request()->get('order', 'desc');
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        $keywords = request()->get('keywords');
       
        $group = request()->get('group');
        if (!empty($group)) {
            $validator = Validator::make(request()->only(['group']), [
                'group' => 'required|alpha_num',
            ], [
                'group.required' => __('分组不能为空'),
                'group.alpha_num' => __('分组格式错误'),
            ]);
            
            $total = ConfigModel::where('group', $group)->count(); 
            $list = ConfigModel::where('group', $group)
                ->offset($start)
                ->limit($limit)
                ->where('title', 'like', '%'.$keywords.'%')
                ->orWhere('name', 'like', '%'.$keywords.'%')
                ->orderBy('listorder', $order)
                ->get()
                ->toArray(); 
        } else {
            $total = ConfigModel::count(); 
            $list = ConfigModel::offset($start)
                ->limit($limit)
                ->where('title', 'like', '%'.$keywords.'%')
                ->orWhere('name', 'like', '%'.$keywords.'%')
                ->orderBy('listorder', $order)
                ->get()
                ->toArray(); 
        }
        
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
        
        $info = ConfigModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('信息不存在'));
        }
        
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
        
        $info = ConfigModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('信息不存在'));
        }
        
        $deleteStatus = ConfigModel::where(['id' => $id])
            ->delete();
        if ($deleteStatus === false) {
            $this->errorJson(__('信息删除失败'));
        }
        
        $this->successJson(__('信息删除成功'));
    }
    
    /**
     * 添加
     * type: text,textarea,number,radio,select,checkbox,array,switch,image
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        $data = request()->all();
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
            $this->errorJson($validator->errors()->first());
        }
        
        $id = md5(mt_rand(100000, 999999).microtime());
        $insertData = [
            'id' => $id,
            'group' => $data['group'],
            'type' => $data['type'],
            'title' => $data['title'],
            'name' => $data['name'],
            'options' => $data['options'] ?? '',
            'value' => $data['value'] ?? '',
            'description' => $data['description'],
            'listorder' => $data['listorder'] ?? 100,
            'is_show' => (request()->get('is_show', 0) == 1) ? 1 : 0,
            'is_system' => (request()->get('is_system', 0) == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
            'update_time' => time(),
            'update_ip' => request()->ip(),
            'create_time' => time(),
            'create_ip' => request()->ip(),
        ];
        
        $status = ConfigModel::insert($insertData);
        if ($status === false) {
            $this->errorJson(__('信息添加失败'));
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
        
        $info = ConfigModel::where('id', '=', $id)
            ->first();
        if (empty($info)) {
            $this->errorJson(__('信息不存在'));
        }
        
        $data = request()->all();
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
            $this->errorJson($validator->errors()->first());
        }
        
        $nameInfo = ConfigModel::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($nameInfo)) {
            $this->errorJson(__('要修改成的名称已经存在'));
        }
        
        $updateData = [
            'group' => $data['group'],
            'title' => $data['title'],
            'title' => $data['title'],
            'options' => $data['options'] ?? '',
            'value' => $data['value'] ?? '',
            'description' => $data['description'],
            'listorder' => $data['listorder'] ? intval($data['listorder']) : 100,
            'is_show' => (isset($data['is_show']) && $data['is_show'] == 1) ? 1 : 0,
            'is_system' => (isset($data['is_system']) && $data['is_system'] == 1) ? 1 : 0,
            'status' => ($data['status'] == 1) ? 1 : 0,
            'update_time' => time(),
            'update_ip' => request()->ip(),
        ];
        
        // 更新信息
        $status = ConfigModel::where('id', $id)
            ->update($updateData);
        if ($status === false) {
            $this->errorJson(__('信息修改失败'));
        }
        
        $this->successJson(__('信息修改成功'));
    }
    
    /**
     * 配置设置
     */
    public function setting()
    {
        $fields = request()->get('fields');
        
        if (!empty($fields)) {
            foreach ($fields as $name => $item) {
                ConfigModel::where([
                    'name' => $name,
                ])->update([
                    'value' => $item,
                ]);
            }
        }
        
        $this->successJson(__('设置更新成功'));
    }
    
}