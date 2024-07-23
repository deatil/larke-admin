<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Support\Tree;
use Larke\Admin\Facade\Permission;
use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\AuthGroup as AuthGroupModel;
use Larke\Admin\Model\AuthRuleAccess as AuthRuleAccessModel;
use Larke\Admin\Model\AuthGroupAccess as AuthGroupAccessModel;
use Larke\Admin\Repository\Admin as AdminRepository;

/**
 * 管理员
 *
 * @create 2020-10-23
 * @author deatil
 */
#[RouteRule(
    title: "管理员", 
    desc:  "系统管理员账号管理",
    order: 115,
    auth:  true,
    slug:  "{prefix}admin"
)]
class Admin extends Base
{
    /**
     * 列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "管理员列表", 
        desc:  "系统管理员账号列表",
        order: 100,
        auth:  true
    )]
    public function index(Request $request)
    {
        $start = (int) $request->input('start', 0);
        $limit = (int) $request->input('limit', 10);
        
        $order = $this->formatOrderBy($request->input('order', 'create_time__ASC'));
        
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
        
        $orWheres = [];
        
        $searchword = $request->input('searchword', '');
        if (! empty($searchword)) {
            $orWheres = [
                ['name', 'like', '%'.$searchword.'%'],
                ['nickname', 'like', '%'.$searchword.'%'],
                ['email', 'like', '%'.$searchword.'%'],
            ];
        }
        
        // 条件
        $query = AdminModel::withAccess()
            ->wheres($wheres)
            ->orWheres($orWheres);
        
        $total = $query->count(); 
        $list = $query->offset($start)
            ->limit($limit)
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
     * 详情
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "管理员详情", 
        desc:  "系统管理员账号详情",
        order: 99,
        auth:  true
    )]
    public function detail(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->with(['groups'])
            ->select([
                'id', 
                'name', 
                'nickname', 
                'email', 
                'avatar', 
                'introduce', 
                'is_root', 
                'status', 
                'last_active', 
                'last_ip',
                'create_time', 
                'create_ip'
            ])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        $adminGroups = $info['groups'];
        unset($info['groupAccesses'], $info['groups']);
        $info['groups'] = collect($adminGroups)
            ->map(function($data) {
                return [
                    'id' => $data['id'],
                    'parentid' => $data['parentid'],
                    'title' => $data['title'],
                    'description' => $data['description'],
                ];
            });
        
        return $this->success(__('larke-admin::common.get_success'), $info);
    }
    
    /**
     * 权限
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "管理员权限", 
        desc:  "系统管理员账号权限",
        order: 98,
        auth:  true
    )]
    public function rules(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->with(['groups'])
            ->select([
                'id', 
                'name', 
                'nickname',
            ])
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        $groupids = collect($info['groups'])->pluck('id')->toArray();
        
        $rules = AdminRepository::getRules($groupids);
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $rules,
        ]);
    }
    
    /**
     * 删除
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "管理员删除", 
        desc:  "系统管理员账号删除",
        order: 97,
        auth:  true
    )]
    public function delete(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($id == $adminid) {
            return $this->error(__('larke-admin::admin.dont_delete_self_passport'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::admin.passport_not_exists'));
        }
        
        if ($info['id'] == config('larkeadmin.auth.admin_id')) {
            return $this->error(__('larke-admin::admin.passport_dont_delete'));
        }
        
        $deleteStatus = $info->delete();
        if ($deleteStatus === false) {
            return $this->error(__('larke-admin::admin.delete_fail'));
        }
        
        return $this->success(__('larke-admin::admin.delete_success'));
    }
    
    /**
     * 添加账号所需分组
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "获取分组", 
        desc:  "添加账号所需分组",
        order: 96,
        auth:  true
    )]
    public function groups(Request $request)
    {
        $isSuperAdministrator = app('larke-admin.auth-admin')->isSuperAdministrator();
        if ($isSuperAdministrator) {
            $list = AuthGroupModel::orderBy('listorder', 'DESC')
                ->orderBy('create_time', 'ASC')
                ->get()
                ->toArray();
            
            $list = collect($list)
                ->map(function($data) {
                    return [
                        'id' => $data['id'],
                        'parentid' => $data['parentid'],
                        'title' => $data['title'],
                        'description' => $data['description'],
                    ];
                })
                ->toArray();
                
            $Tree = new Tree();
            $list = $Tree
                ->withConfig('buildChildKey', 'children')
                ->withData($list)
                ->build(0);
            
            $list = $Tree->buildFormatList($list);
        } else {
            $list = app('larke-admin.auth-admin')->getGroupChildren();
        }
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
    }
    
    /**
     * 添加
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "管理员添加", 
        desc:  "系统管理员账号添加",
        order: 95,
        auth:  true
    )]
    public function create(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'group_id' => 'required',
            'name' => 'required|min:2|max:20|unique:'.AdminModel::class,
            'nickname' => 'required|min:2|max:150',
            'email' => 'required|email|min:5|max:100|unique:'.AdminModel::class,
            'introduce' => 'required|max:500',
            'status' => 'required',
        ], [
            'group_id.required' => __('larke-admin::admin.group_dont_empty'),
            'name.required' => __('larke-admin::admin.passport_dont_empty'),
            'name.unique' => __('larke-admin::admin.passport_exists'),
            'name.min' => __('larke-admin::admin.name_min'),
            'name.max' => __('larke-admin::admin.name_max'),
            'nickname.required' => __('larke-admin::admin.nickname_dont_empty'),
            'nickname.min' => __('larke-admin::admin.nickname_min'),
            'nickname.max' => __('larke-admin::admin.nickname_max'),
            'email.required' => __('larke-admin::admin.email_dont_empty'),
            'email.email' => __('larke-admin::admin.email_error'),
            'email.unique' => __('larke-admin::admin.email_exists'),
            'email.min' => __('larke-admin::admin.email_min'),
            'email.max' => __('larke-admin::admin.email_max'),
            'introduce.required' => __('larke-admin::admin.introduce_dont_empty'),
            'introduce.max' => __('larke-admin::admin.introduce_max'),
            'status.required' => __('larke-admin::admin.status_dont_empty'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $insertData = [
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'introduce' => $data['introduce'],
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        if (!empty($data['avatar'])) {
            $validatorAvatar = Validator::make([
                'avatar' => $data['avatar'],
            ], [
                'avatar' => 'required|size:36',
            ], [
                'avatar.required' => __('larke-admin::admin.avatar_dont_empty'),
                'avatar.size' => __('larke-admin::admin.avatar_error'),
            ]);

            if ($validatorAvatar->fails()) {
                return $this->error($validatorAvatar->errors()->first());
            }

            $insertData['avatar'] = $data['avatar'];
        }
        
        $admin = AdminModel::create($insertData);
        if ($admin === false) {
            return $this->error(__('larke-admin::admin.create_passport_fail'));
        }
        
        // 添加关联
        AuthGroupAccessModel::create([
            'admin_id' => $admin->id,
            'group_id' => $data['group_id'],
        ]);
        
        return $this->success(__('larke-admin::admin.create_passport_success'), [
            'id' => $admin->id,
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
        title: "管理员更新", 
        desc:  "系统管理员账号更新",
        order: 94,
        auth:  true
    )]
    public function update(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($id == $adminid) {
            return $this->error(__('larke-admin::admin.dont_update_passport'));
        }
        
        $adminInfo = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => 'required|max:20',
            'nickname' => 'required|max:150',
            'email' => 'required|email|max:100',
            'introduce' => 'required|max:500',
            'status' => 'required',
        ], [
            'name.required' => __('larke-admin::admin.name_required'),
            'nickname.required' => __('larke-admin::admin.nickname_dont_empty'),
            'email.required' => __('larke-admin::admin.email_dont_empty'),
            'email.email' => __('larke-admin::admin.email_error'),
            'introduce.required' => __('larke-admin::admin.introduce_dont_empty'),
            'introduce.max' => __('larke-admin::admin.introduce_too_long'),
            'status.required' => __('larke-admin::admin.status_dont_empty'),
        ]);
        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $nameInfo = AdminModel::orWhere(function($query) use($id, $data) {
                $query->where('id', '!=', $id);
                $query->where('name', '=', $data['name']);
            })
            ->orWhere(function($query) use($id, $data) {
                $query->where('id', '!=', $id);
                $query->where('email', '=', $data['email']);
            })
            ->first();
        if (! empty($nameInfo)) {
            return $this->error(__('larke-admin::admin.name_or_email_exists'));
        }
        
        $updateData = [
            'name' => $data['name'],
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'introduce' => $data['introduce'],
            'status' => ($data['status'] == 1) ? 1 : 0,
        ];
        
        if (! empty($data['avatar'])) {
            $validatorAvatar = Validator::make([
                'avatar' => $data['avatar'],
            ], [
                'avatar' => 'required|size:36',
            ], [
                'avatar.required' => __('larke-admin::admin.avatar_dont_empty'),
                'avatar.size' => __('larke-admin::admin.avatar_error'),
            ]);

            if ($validatorAvatar->fails()) {
                return $this->error($validatorAvatar->errors()->first());
            }

            $updateData['avatar'] = $data['avatar'];
        }

        // 更新信息
        $status = $adminInfo->update($updateData);
        if ($status === false) {
            return $this->error(__('larke-admin::admin.update_fail'));
        }
        
        return $this->success(__('larke-admin::admin.update_success'));
    }

    /**
     * 修改头像
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "修改头像", 
        desc:  "系统管理员账号修改头像",
        order: 93,
        auth:  true
    )]
    public function updateAvatar(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($id == $adminid) {
            return $this->error(__('larke-admin::admin.dont_update_passport'));
        }
        
        $adminInfo = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        $data = $request->only(['avatar']);
        
        $validator = Validator::make($data, [
            'avatar' => 'required|size:36',
        ], [
            'avatar.required' => __('larke-admin::admin.avatar_dont_empty'),
            'avatar.size' => __('larke-admin::admin.avatar_error'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $status = $adminInfo->updateAvatar($data['avatar']);
        if ($status === false) {
            return $this->error(__('larke-admin::admin.update_avatar_fail'));
        }
        
        return $this->success(__('larke-admin::admin.update_avatar_success'));
    }
    
    /**
     * 修改密码
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "修改密码", 
        desc:  "系统管理员账号修改密码",
        order: 92,
        auth:  true
    )]
    public function updatePasssword(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($id == $adminid) {
            return $this->error(__('larke-admin::admin.dont_update_passport'));
        }
        
        $adminInfo = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($adminInfo)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }

        // 密码长度错误
        $password = $request->input('password');
        if (strlen($password) != 32) {
            return $this->error(__('larke-admin::admin.password_error'));
        }

        // 新密码
        $newPasswordInfo = AdminModel::makePassword($password); 

        // 更新信息
        $status = $adminInfo->update([
                'password' => $newPasswordInfo['password'],
                'password_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            return $this->error(__('larke-admin::admin.password_update_fail'));
        }
        
        return $this->success(__('larke-admin::admin.password_update_success'));
    }
    
    /**
     * 启用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "管理员启用", 
        desc:  "系统管理员账号启用",
        order: 91,
        auth:  true
    )]
    public function enable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($id == $adminid) {
            return $this->error(__('larke-admin::admin.dont_update_passport'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        if ($info->status == 1) {
            return $this->error(__('larke-admin::admin.passport_enabled'));
        }
        
        $status = $info->enable();
        if ($status === false) {
            return $this->error(__('larke-admin::admin.passport_enable_fail'));
        }
        
        return $this->success(__('larke-admin::admin.passport_enable_success'));
    }
    
    /**
     * 禁用
     *
     * @param string $id
     * @return Response
     */
    #[RouteRule(
        title: "管理员禁用", 
        desc:  "系统管理员账号禁用",
        order: 90,
        auth:  true
    )]
    public function disable(string $id)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($id == $adminid) {
            return $this->error(__('larke-admin::admin.dont_update_passport'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        if ($info->status == 0) {
            return $this->error(__('larke-admin::admin.passport_disabled'));
        }
        
        $status = $info->disable();
        if ($status === false) {
            return $this->error(__('larke-admin::admin.passport_disable_fail'));
        }
        
        return $this->success(__('larke-admin::admin.passport_disable_success'));
    }
    
    /**
     * 退出
     *
     * @param string $refreshToken
     * @return Response
     */
    #[RouteRule(
        title: "管理员退出", 
        desc:  "系统管理员账号退出，添加用户的 refreshToken 到黑名单",
        order: 89,
        auth:  true
    )]
    public function logout(string $refreshToken)
    {
        if (empty($refreshToken)) {
            return $this->error(__('larke-admin::admin.refresh_token_not_empty'));
        }
        
        if (app('larke-admin.cache')->has(md5($refreshToken))) {
            return $this->error(__('larke-admin::admin.refresh_token_error'));
        }
        
        try {
            $decodeRefreshToken = app('larke-admin.auth-token')
                ->decodeRefreshToken($refreshToken);
            
            // 验证
            app('larke-admin.auth-token')->validate($decodeRefreshToken);
            
            // 签名
            app('larke-admin.auth-token')->verify($decodeRefreshToken);
            
            $refreshAdminid = $decodeRefreshToken->getData('adminid');
            
            // 过期时间
            $refreshTokenExpiresIn = $decodeRefreshToken->getClaim('exp')->getTimestamp() - $decodeRefreshToken->getClaim('iat')->getTimestamp();
        } catch(\Exception $e) {
            return $this->error($e->getMessage());
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        if ($refreshAdminid == $adminid) {
            return $this->error(__('larke-admin::admin.self_passport_dont_logout'));
        }
        
        // 添加缓存黑名单
        app('larke-admin.cache')->add(md5($refreshToken), time(), $refreshTokenExpiresIn);
        
        // 更新刷新时间
        AdminModel::where('id', $refreshAdminid)->update([
            'refresh_time' => time(), 
            'refresh_ip' => request()->ip(),
        ]);
        
        return $this->success(__('larke-admin::admin.passport_logout_success'));
    }
    
    /**
     * 授权
     *
     * @param string $id
     * @param Request $request
     * @return Response
     */
    #[RouteRule(
        title: "管理员授权", 
        desc:  "系统管理员账号授权",
        order: 88,
        auth:  true
    )]
    public function access(string $id, Request $request)
    {
        if (empty($id)) {
            return $this->error(__('larke-admin::admin.userid_dont_empty'));
        }
        
        $info = AdminModel::withAccess()
            ->where('id', '=', $id)
            ->first();
        if (empty($info)) {
            return $this->error(__('larke-admin::admin.user_not_exists'));
        }
        
        // 删除
        AuthGroupAccessModel::where([
            'admin_id' => $id,
        ])->delete();
        
        // 删除权限缓存数据
        Permission::deleteRolesForUser($id);
        
        $access = $request->input('access');
        if (! empty($access)) {
            $groupIds = app('larke-admin.auth-admin')->getGroupChildrenIds();
            $accessIds = explode(',', $access);
            $accessIds = collect($accessIds)->unique();
            
            // 取交集
            if (!app('larke-admin.auth-admin')->isSuperAdministrator()) {
                $intersectAccess = array_intersect_assoc($groupIds, $accessIds);
            } else {
                $intersectAccess = $accessIds;
            }
            
            // 批量添加
            $newData = [];
            foreach ($intersectAccess as $value) {
                $newData[] = [
                    'id' => AuthGroupAccessModel::uuid(),
                    'admin_id' => $id,
                    'group_id' => $value,
                ];
            }
            
            AuthGroupAccessModel::insertAll($newData);
            
            // 批量赋值授权
            $roles = AuthGroupModel::whereIn("id", $intersectAccess)
                ->where('status', 1)
                ->distinct()
                ->select()
                ->get()
                ->pluck("id")
                ->all();
            Permission::addRolesForUser($id, $roles);
        }
        
        return $this->success(__('larke-admin::admin.group_access_success'));
    }
    
    /**
     * 重设权限缓存
     *
     * @return Response
     */
    #[RouteRule(
        title: "重设权限缓存", 
        desc:  "重设权限缓存",
        order: 87,
        auth:  true
    )]
    public function ResetPermission()
    {
        // 清空原始数据
        $guard = config('larkeauth.default');
        $table = config('larkeauth.guards.'.$guard.'.database.rules_table');
        if (empty($table)) {
            return $this->error(__('larke-admin::admin.reset_permission_fail'));
        }
        
        DB::table($table)->truncate();
        
        // 规则权限
        $rules = AuthRuleAccessModel::with('rule')
            ->whereHas('rule', function($query) {
                $query->where('status', 1);
            })
            ->select()
            ->get()
            ->each(function($data) {
                Permission::addPolicy($data['group_id'], $data['rule']['slug'], strtoupper($data['rule']['method']));
            });
        
        // 分组权限
        $groups = AuthGroupAccessModel::with('group')
            ->whereHas('group', function($query) {
                $query->where('status', 1);
            })
            ->select()
            ->get()
            ->each(function($data) {
                Permission::addRoleForUser($data['admin_id'], $data['group_id']);
            });
        
        return $this->success(__('larke-admin::admin.reset_permission_success'));
    }

}
