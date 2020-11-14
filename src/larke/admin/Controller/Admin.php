<?php

namespace Larke\Admin\Controller;

use Carbon\Carbon;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;
use Larke\Admin\Service\Password as PasswordService;
use Larke\Admin\Repository\Admin as AdminRepository;

/**
 * 账号
 *
 * @title 账号
 * @desc 系统账号管理
 * @order 105
 * @auth true
 *
 * @create 2020-10-23
 * @author deatil
 */
class Admin extends Base
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
        
        $order = $request->get('order', 'DESC');
        if (!in_array(strtoupper($order), ['ASC', 'DESC'])) {
            $order = 'DESC';
        }
        
        $wheres = [];
        
        $status = $this->switchStatus($request->get('status'));
        if ($status !== false) {
            $wheres[] = ['status', $status];
        }
        
        $orWheres = [];
        
        $searchword = $request->get('searchword', '');
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['nickname', 'like', '%'.$searchword.'%'],
                ['email', 'like', '%'.$searchword.'%'],
            ];
        }
        
        $total = AdminModel::withAccess()
            ->count(); 
        $list = AdminModel::withAccess()
            ->offset($start)
            ->limit($limit)
            ->orWheres($orWheres)
            ->wheres($wheres)
            ->select(
                'id', 
                'name', 
                'nickname', 
                'email', 
                'avatar', 
                'is_root', 
                'status', 
                'last_active', 
                'last_ip',
                'create_time', 
                'create_ip'
            )
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
     * @param  Request  $request
     * @return Response
     */
    public function detail(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AdminModel::withAccess()
            ->where(['id' => $id])
            ->with(['groups'])
            ->select([
                'id', 
                'name', 
                'nickname', 
                'email', 
                'avatar', 
                'is_root', 
                'status', 
                'last_active', 
                'last_ip',
                'create_time', 
                'create_ip'
            ])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('账号信息不存在'));
        }
        
        $adminGroups = $info['groups'];
        unset($info['groupAccesses'], $info['groups']);
        $info['groups'] = collect($adminGroups)->map(function($data) {
            return [
                'id' => $data['id'],
                'parentid' => $data['parentid'],
                'title' => $data['title'],
                'description' => $data['description'],
            ];
        });
        
        return $this->successJson(__('获取成功'), $info);
    }
    
    /**
     * 权限
     *
     * @param  Request  $request
     * @return Response
     */
    public function rules(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AdminModel::withAccess()
            ->where(['id' => $id])
            ->with(['groups'])
            ->select([
                'id', 
                'name', 
                'nickname',
            ])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('账号信息不存在'));
        }
        
        $groupids = collect($info['groups'])->pluck('id')->toArray();
        
        $rules = AdminRepository::getRules($groupids);
        return $this->successJson(__('获取成功'), [
            'list' => $rules,
        ]);
    }
    
    /**
     * 删除
     *
     * @param  Request  $request
     * @return Response
     */
    public function delete(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($id == $adminid) {
            return $this->errorJson(__('你不能删除你自己'));
        }
        
        $info = AdminModel::withAccess()
            ->where(['id' => $id])
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('账号信息不存在'));
        }
        
        if ($info['id'] == config('larke.auth.admin_id')) {
            return $this->errorJson(__('当前账号不能被删除'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->errorJson(__('账号删除失败'));
        }
        
        return $this->successJson(__('账号删除成功'));
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
            'name' => 'required|max:20|unique:'.AdminModel::class,
            'nickname' => 'required|max:150',
            'email' => 'required|email|max:100',
            'status' => 'required',
        ], [
            'name.required' => __('账号不能为空'),
            'name.unique' => __('name 已经存在'),
            'nickname.required' => __('昵称不能为空'),
            'email.required' => __('邮箱不能为空'),
            'email.email' => __('邮箱格式错误'),
            'status.required' => __('状态选项不能为空'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $insertData = [
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'status' => ($data['status'] == 1) ? 1 : 0,
            'is_root' => (isset($data['is_root']) && $data['is_root'] == 1) ? 1 : 0,
        ];
        if (!empty($data['avatar'])) {
            $insertData['avatar'] = $data['avatar'];
        }
        
        $admin = AdminModel::create($insertData);
        if ($admin === false) {
            return $this->errorJson(__('添加信息失败'));
        }
        
        // 用户组默认取当前用户的用户组的其中之一
        $groupIds = app('larke.admin')->getGroupids();
        if (count($groupIds) > 0) {
            AuthGroupAccessModel::create([
                'admin_id' => $admin->id,
                'group_id' => $groupIds[0],
            ]);
        }
        
        return $this->successJson(__('添加信息成功'), [
            'id' => $admin->id,
        ]);
    }
    
    /**
     * 更新
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($id == $adminid) {
            return $this->errorJson(__('你不能修改自己的信息'));
        }
        
        $adminInfo = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            return $this->errorJson(__('要修改的账号不存在'));
        }
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|max:20',
            'nickname' => 'required|max:150',
            'email' => 'required|email|max:100',
        ], [
            'name.required' => __('账号不能为空'),
            'nickname.required' => __('昵称不能为空'),
            'email.required' => __('邮箱不能为空'),
            'email.email' => __('邮箱格式错误'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $nameInfo = AdminModel::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($nameInfo)) {
            return $this->errorJson(__('要修改成的管理账号已经存在'));
        }
        
        $updateData = [
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'status' => ($data['status'] == 1) ? 1 : 0,
            'is_root' => (isset($data['is_root']) && $data['is_root'] == 1) ? 1 : 0,
        ];
        
        // 更新信息
        $status = AdminModel::where('id', $id)
            ->update($updateData);
        if ($status === false) {
            return $this->errorJson(__('信息修改失败'));
        }
        
        return $this->successJson(__('信息修改成功'));
    }

    /**
     * 修改头像
     */
    public function updateAvatar(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($id == $adminid) {
            return $this->errorJson(__('你不能修改自己的头像'));
        }
        
        $adminInfo = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            return $this->errorJson(__('要修改的账号不存在'));
        }
        
        $data = $request->only(['avatar']);
        
        $validator = Validator::make($data, [
            'avatar' => 'required|size:32',
        ], [
            'avatar.required' => __('头像数据不能为空'),
            'avatar.size' => __('头像数据错误'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $status = $adminInfo->updateAvatar($data['avatar']);
        if ($status === false) {
            return $this->errorJson(__('修改信息失败'));
        }
        
        return $this->successJson(__('修改头像成功'));
    }
    
    /**
     * 修改密码
     *
     * @param  Request  $request
     * @return Response
     */
    public function updatePasssword(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('账号ID不能为空'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($id == $adminid) {
            return $this->errorJson(__('你不能修改自己的密码'));
        }
        
        $adminInfo = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            return $this->errorJson(__('要修改的账号不存在'));
        }

        // 密码长度错误
        $password = $request->get('password');
        if (strlen($password) != 32) {
            return $this->errorJson(__('密码格式错误'));
        }

        // 新密码
        $newPasswordInfo = (new PasswordService())
            ->withSalt(config('larke.passport.password_salt'))
            ->encrypt($password); 

        // 更新信息
        $status = AdminModel::where('id', $adminInfo['id'])
            ->update([
                'password' => $newPasswordInfo['password'],
                'password_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            return $this->errorJson(__('密码修改失败'));
        }
        
        return $this->successJson(__('密码修改成功'));
    }
    
    /**
     * 启用
     *
     * @param  Request  $request
     * @return Response
     */
    public function enable(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($id == $adminid) {
            return $this->errorJson(__('你不能修改自己的账号'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('账号不存在'));
        }
        
        if ($info->status == 1) {
            return $this->errorJson(__('账号已启用'));
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
    public function disable(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($id == $adminid) {
            return $this->errorJson(__('你不能修改自己的账号'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('账号不存在'));
        }
        
        if ($info->status == 0) {
            return $this->errorJson(__('账号已禁用'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->errorJson(__('禁用失败'));
        }
        
        return $this->successJson(__('禁用成功'));
    }
    
    /**
     * 退出
     * 
     * 添加用户的 refreshToken 到黑名单
     */
    public function logout(string $refreshToken, Request $request)
    {
        if (empty($refreshToken)) {
            return $this->errorJson(__('refreshToken不能为空'));
        }
        
        if (app('larke.cache')->has(md5($refreshToken))) {
            return $this->errorJson(__('refreshToken已失效'));
        }
        
        $refreshJwt = app('larke.jwt')
            ->withJti(config('larke.passport.refresh_token_id'))
            ->withToken($refreshToken)
            ->decode();
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            return $this->errorJson(__('refreshToken已过期'));
        }
        
        $refreshAdminid = $refreshJwt->getClaim('adminid');
        if ($refreshAdminid === false) {
            return $this->errorJson(__('token错误'));
        }
        
        $adminid = app('larke.admin')->getId();
        if ($refreshAdminid == $adminid) {
            return $this->errorJson(__('你不能退出你的 refreshToken ！'));
        }
        
        $refreshTokenExpiredIn = $refreshJwt->getClaim('exp') - $refreshJwt->getClaim('iat');
        
        // 添加缓存黑名单
        app('larke.cache')->add(md5($refreshToken), 'out', $refreshTokenExpiredIn);
        
        return $this->successJson(__('退出成功'));
    }
    
    /**
     * 授权
     *
     * @param  Request  $request
     * @return Response
     */
    public function access(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->errorJson(__('ID不能为空'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->errorJson(__('信息不存在'));
        }
        
        AuthGroupAccessModel::where([
                'admin_id' => $id,
            ])
            ->get()
            ->each
            ->delete();
        
        $access = $request->get('access');
        if (!empty($access)) {
            $groupIds = app('larke.admin')->getGroupChildrenIds();
            $accessIds = explode(',', $access);
            $accessIds = collect($accessIds)->unique();
            
            // 取交集
            if (!app('larke.admin')->isAdministrator()) {
                $intersectAccess = array_intersect_assoc($groupIds, $accessIds);
            } else {
                $intersectAccess = $accessIds;
            }
            
            $accessData = [];
            foreach ($intersectAccess as $value) {
                AuthGroupAccessModel::create([
                    'admin_id' => $id,
                    'group_id' => $value,
                ]);
            }
        }
        
        return $this->successJson(__('授权分组成功'));
    }
    
}