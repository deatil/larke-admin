<?php

declare (strict_types = 1);

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

use Larke\Admin\Annotation\RouteRule;
use Larke\Admin\Model\Admin as AdminModel;

/**
 * 个人信息
 *
 * @create 2020-10-20
 * @author deatil
 */
#[RouteRule(
    title: "个人信息", 
    desc:  "个人信息管理",
    order: 150,
    auth:  true,
    slug:  "{prefix}profile"
)]
class Profile extends Base
{
    /**
     * 我的信息
     *
     * @return Response
     */
    #[RouteRule(
        title: "我的信息", 
        desc:  "我的信息管理",
        order: 151,
        auth:  true
    )]
    public function index()
    {
        $data = app('larke-admin.auth-admin')->getProfile();
        
        return $this->success(__('larke-admin::common.get_success'), $data);
    }
    
    /**
     * 修改我的信息
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "修改我的信息", 
        desc:  "修改我的信息管理",
        order: 152,
        auth:  true
    )]
    public function update(Request $request)
    {
        $data = $request->only(['nickname', 'email', 'introduce']);
        
        $validator = Validator::make($data, [
            'nickname' => 'required|max:150',
            'email' => 'required|email|max:100',
            'introduce' => 'required|max:500',
        ], [
            'nickname.required' => __('larke-admin::profile.nickname_required'),
            'nickname.max' => __('larke-admin::profile.nickname_max'),
            'email.required' => __('larke-admin::profile.email_required'),
            'email.email' => __('larke-admin::profile.email_error'),
            'email.max' => __('larke-admin::profile.email_max'),
            'introduce.required' => __('larke-admin::profile.introduce_required'),
            'introduce.max' => __('larke-admin::profile.introduce_max'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $updateData = [
            'nickname' => $data['nickname'],
            'email' => $data['email'],
            'introduce' => $data['introduce'],
        ];
        
        // 更新信息
        $adminid = app('larke-admin.auth-admin')->getId();
        $status = AdminModel::where('id', $adminid)
            ->update($updateData);
        if ($status === false) {
            return $this->error(__('larke-admin::profile.update_fail'));
        }
        
        return $this->success(__('larke-admin::profile.update_success'));
    }

    /**
     * 修改头像
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "修改头像", 
        desc:  "修改头像管理",
        order: 153,
        auth:  true
    )]
    public function updateAvatar(Request $request)
    {
        $data = $request->only(['avatar']);
        
        $validator = Validator::make($data, [
            'avatar' => 'required|size:36',
        ], [
            'avatar.required' => __('larke-admin::profile.avatar_dont_empty'),
            'avatar.size' => __('larke-admin::profile.avatar_error'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        $status = AdminModel::where('id', $adminid)
            ->first()
            ->updateAvatar($data['avatar']);
        if ($status === false) {
            return $this->error(__('larke-admin::profile.update_avatar_fail'));
        }
        
        return $this->success(__('larke-admin::profile.update_avatar_success'));
    }

    /**
     * 修改密码
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "修改密码", 
        desc:  "修改密码管理",
        order: 154,
        auth:  true
    )]
    public function updatePasssword(Request $request)
    {
        // 密码长度错误
        $oldPassword = $request->input('oldpassword');
        if (strlen($oldPassword) != 32) {
            return $this->error(__('larke-admin::profile.old_password_error'));
        }

        // 密码长度错误
        $newPassword = $request->input('newpassword');
        if (strlen($newPassword) != 32) {
            return $this->error(__('larke-admin::profile.new_password_error'));
        }

        $newPasswordConfirm = $request->input('newpassword_confirm');
        if (strlen($newPasswordConfirm) != 32) {
            return $this->error(__('larke-admin::profile.password_confirm_error'));
        }

        if ($newPassword != $newPasswordConfirm) {
            return $this->error(__('larke-admin::profile.two_password_dont_equal'));
        }

        $adminid = app('larke-admin.auth-admin')->getId();
        $adminInfo = AdminModel::where('id', $adminid)
            ->first();
        if (empty($adminInfo)) {
            return $this->error(__('larke-admin::profile.passport_dont_exists'));
        }
        
        $adminInfo = $adminInfo->makeVisible(['password', 'password_salt']);
        $encryptPassword = AdminModel::checkPassword($adminInfo->toArray(), $oldPassword); 
        if (! $encryptPassword) {
            return $this->error(__('larke-admin::profile.password_error'));
        }

        // 新密码
        $newPasswordInfo = AdminModel::makePassword($newPassword); 

        // 更新信息
        $status = $adminInfo->update([
                'password' => $newPasswordInfo['password'],
                'password_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            return $this->error(__('larke-admin::profile.update_password_fail'));
        }
        
        return $this->success(__('larke-admin::profile.update_password_success'));
    }

    /**
     * 权限列表
     *
     * @return Response
     */
    #[RouteRule(
        title: "权限列表", 
        desc:  "权限列表管理",
        order: 155,
        auth:  true
    )]
    public function rules()
    {
        $rules = app('larke-admin.auth-admin')->getRules();
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $rules,
        ]);
    }
    
    /**
     * 权限列表
     *
     * @param  Request  $request
     * @return Response
     */
    #[RouteRule(
        title: "权限列表配置", 
        desc:  "权限列表配置",
        order: 208,
        auth:  true
    )]
    public function roles()
    {
        $list = app('larke-admin.auth-admin')->getRuleSlugs();
        
        return $this->success(__('larke-admin::common.get_success'), [
            'list' => $list,
        ]);
    }

}
