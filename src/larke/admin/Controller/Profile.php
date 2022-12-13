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
        
        return $this->success(__('获取成功'), $data);
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
            'nickname.required' => __('昵称不能为空'),
            'nickname.max' => __('昵称字数超过了限制'),
            'email.required' => __('邮箱不能为空'),
            'email.email' => __('邮箱格式错误'),
            'email.max' => __('邮箱字数超过了限制'),
            'introduce.required' => __('简介不能为空'),
            'introduce.max' => __('简介字数超过了限制'),
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
            return $this->error(__('修改信息失败'));
        }
        
        return $this->success(__('修改信息成功'));
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
            'avatar.required' => __('头像数据不能为空'),
            'avatar.size' => __('头像数据错误'),
        ]);

        if ($validator->fails()) {
            return $this->error($validator->errors()->first());
        }
        
        $adminid = app('larke-admin.auth-admin')->getId();
        $status = AdminModel::where('id', $adminid)
            ->first()
            ->updateAvatar($data['avatar']);
        if ($status === false) {
            return $this->error(__('修改信息失败'));
        }
        
        return $this->success(__('修改头像成功'));
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
            return $this->error(__('旧密码错误'));
        }

        // 密码长度错误
        $newPassword = $request->input('newpassword');
        if (strlen($newPassword) != 32) {
            return $this->error(__('新密码错误'));
        }

        $newPasswordConfirm = $request->input('newpassword_confirm');
        if (strlen($newPasswordConfirm) != 32) {
            return $this->error(__('确认密码错误'));
        }

        if ($newPassword != $newPasswordConfirm) {
            return $this->error(__('两次密码输入不一致'));
        }

        $adminid = app('larke-admin.auth-admin')->getId();
        $adminInfo = AdminModel::where('id', $adminid)
            ->first();
        if (empty($adminInfo)) {
            return $this->error(__('帐号错误'));
        }
        
        $adminInfo = $adminInfo->makeVisible(['password', 'password_salt']);
        $encryptPassword = AdminModel::checkPassword($adminInfo->toArray(), $oldPassword); 
        if (! $encryptPassword) {
            return $this->error(__('用户密码错误'));
        }

        // 新密码
        $newPasswordInfo = AdminModel::makePassword($newPassword); 

        // 更新信息
        $status = $adminInfo->update([
                'password' => $newPasswordInfo['password'],
                'password_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            return $this->error(__('密码修改失败'));
        }
        
        return $this->success(__('密码修改成功'));
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
        
        return $this->success(__('获取成功'), [
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
        
        return $this->success(__('获取成功'), [
            'list' => $list,
        ]);
    }

}
