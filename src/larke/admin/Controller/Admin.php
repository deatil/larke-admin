<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;
use Larke\Admin\Service\Password as PasswordService;

/**
 * 账号
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
        
        $total = AdminModel::count(); 
        $list = AdminModel::offset($start)
            ->limit($limit)
            ->select(
                'id', 
                'name', 
                'nickname', 
                'email', 
                'avatar', 
                'status', 
                'last_active', 
                'last_ip',
                'create_time', 
                'create_ip'
            )
            ->orderBy('create_time', $order)
            ->get()
            ->toArray(); 
        
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
            $this->errorJson(__('账号ID不能为空'));
        }
        
        $info = AdminModel::where(['id' => $id])
            ->select(
                'id', 
                'name', 
                'nickname', 
                'email', 
                'avatar', 
                'status', 
                'last_active', 
                'last_ip',
                'create_time', 
                'create_ip'
            )
            ->first();
        if (empty($info)) {
            $this->errorJson(__('账号信息不存在'));
        }
        
        $groupAccesses = collect($info['groupAccesses'])->map(function($data) {
            return $data['group_id'];
        });
        unset($info['groupAccesses']);
        $info['group_accesses'] = $groupAccesses;
        
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
            $this->errorJson(__('账号ID不能为空'));
        }
        $adminid = config('larke.auth.adminid');
        if ($id == $adminid) {
            $this->errorJson(__('你不能删除你自己'));
        }
        
        $info = AdminModel::where(['id' => $id])
            ->first();
        if (empty($info)) {
            $this->errorJson(__('账号信息不存在'));
        }
        
        $deleteStatus = AdminModel::where(['id' => $id])
            ->delete();
        if ($deleteStatus === false) {
            $this->errorJson(__('账号删除失败'));
        }
        
        $this->successJson(__('账号删除成功'));
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
            $this->errorJson($validator->errors()->first());
        }
        
        $id = md5(mt_rand(100000, 999999).microtime());
        $insertData = [
            'id' => $id,
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'status' => ($data['status'] == 1) ? 1 : 0,
            'last_active' => time(),
            'last_ip' => $request->ip(),
            'create_time' => time(),
            'create_ip' => $request->ip(),
        ];
        if (!empty($data['avatar'])) {
            $insertData['avatar'] = $data['avatar'];
        }
        
        $status = AdminModel::insertGetid($insertData);
        if ($status === false) {
            $this->errorJson(__('添加信息失败'));
        }
        
        if (isset($data['access'])) {
            $accessData = [];
            $accessArr = explode(',', $data['access']);
            foreach ($accessArr as $access) {
                $accessData[] = [
                    'admin_id' => $id,
                    'group_id' => $access,
                ];
            }
            AuthGroupAccessModel::insert($accessData);
        }
        
        $this->successJson(__('添加信息成功'), [
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
        
        $adminid = config('larke.auth.adminid');
        if ($id == $adminid) {
            $this->errorJson(__('你不能修改自己的信息'));
        }
        
        $adminInfo = AdminModel::where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            $this->errorJson(__('要修改的账号不存在'));
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
            $this->errorJson($validator->errors()->first());
        }
        
        $nameInfo = AdminModel::where('name', $data['name'])
            ->where('id', '!=', $id)
            ->first();
        if (!empty($nameInfo)) {
            $this->errorJson(__('要修改成的管理账号已经存在'));
        }
        
        $updateData = [
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        if (!empty($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }
        
        // 更新信息
        $status = AdminModel::where('id', $id)
            ->update($updateData);
        if ($status === false) {
            $this->errorJson(__('信息修改失败'));
        }
        
        if (isset($data['access'])) {
            AuthGroupAccessModel::where(['admin_id' => $id])->delete();
            
            $accessData = [];
            $accessArr = explode(',', $data['access']);
            foreach ($accessArr as $access) {
                $accessData[] = [
                    'admin_id' => $id,
                    'group_id' => $access,
                ];
            }
            AuthGroupAccessModel::insert($accessData);
        }
        
        $this->successJson(__('信息修改成功'));
    }
    
    /**
     * 修改密码
     *
     * @param  Request  $request
     * @return Response
     */
    public function changePasssword(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            $this->errorJson(__('账号ID不能为空'));
        }
        
        $adminid = config('larke.auth.adminid');
        if ($id == $adminid) {
            $this->errorJson(__('你不能修改自己的密码'));
        }
        
        $adminInfo = AdminModel::where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            $this->errorJson(__('要修改的账号不存在'));
        }

        // 密码长度错误
        $password = $request->get('password');
        if (strlen($password) != 32) {
            $this->errorJson(__('密码格式错误'));
        }

        // 新密码
        $newPasswordInfo = (new PasswordService())
            ->withSalt(config('larke.passport.salt'))
            ->encrypt($password); 

        // 更新信息
        $status = AdminModel::where('id', $adminInfo['id'])
            ->update([
                'password' => $newPasswordInfo['password'],
                'passport_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            $this->errorJson(__('密码修改失败'));
        }
        
        $this->successJson(__('密码修改成功'));
    }
    
    /**
     * 退出
     */
    public function logout(Request $request)
    {
        $id = $request->get('id');
        if (empty($id)) {
            $this->errorJson(__('账号ID不能为空'));
        }
        
        $adminid = config('larke.auth.adminid');
        if ($id == $adminid) {
            $this->errorJson(__('你不能修改退出你的登陆'));
        }
        
        $refreshToken = $request->get('refresh_token');
        if (empty($refreshToken)) {
            $this->errorJson(__('refreshToken不能为空'));
        }
        
        $adminInfo = AdminModel::where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            $this->errorJson(__('账号不存在'));
        }
        
        if (Cache::has(md5($refreshToken))) {
            $this->errorJson(__('refreshToken已失效'));
        }
        
        $refreshJwt = app('larke.jwt');
        
        try {
            $refreshJwt->withToken($refreshToken)->decode();
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            $this->errorJson(__('refreshToken已过期'));
        }
        
        try {
            $refreshAdminid = $refreshJwt->getClaim('adminid');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        try {
            $refreshTokenExpiredIn = $refreshJwt->getClaim('expired_in');
        } catch(\Exception $e) {
            $this->errorJson(__("JWT格式错误：:message", [
                'message' => $e->getMessage(),
            ]));
        }
        
        if ($id != $refreshAdminid) {
            $this->errorJson(__('退出失败'));
        }
        
        // 添加缓存黑名单
        Cache::put(md5($refreshToken), $refreshToken, $refreshTokenExpiredIn);
        
        $this->successJson(__('退出成功'));
    }
    
}