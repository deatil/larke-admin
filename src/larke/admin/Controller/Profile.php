<?php

namespace Larke\Admin\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

use Larke\Admin\Model\Admin as AdminModel;
use Larke\Admin\Model\Attachment as AttachmentModel;
use Larke\Admin\Service\Password as PasswordService;

/**
 * 个人信息
 *
 * @create 2020-10-20
 * @author deatil
 */
class Profile extends Base
{
    /**
     * 我的信息
     */
    public function index(Request $request)
    {
        $adminid = app('larke.auth')->getId();
        $adminInfo = AdminModel::select(
                "name",
                "nickname",
                "avatar",
                "email",
                "last_active"
            )
            ->where('id', $adminid)
            ->first();
        
        if (empty($adminInfo)) {
            return $this->errorJson(__('帐号错误'));
        }
        
        return $this->successJson(__('获取成功'), $adminInfo);
    }
    
    /**
     * 修改我的信息
     */
    public function update(Request $request)
    {
        $data = $request->only(['nickname', 'email', 'avatar']);
        
        $validator = Validator::make($data, [
            'nickname' => 'required|max:150',
            'email' => 'required|email|max:100',
        ], [
            'nickname.required' => __('昵称不能为空'),
            'email.required' => __('邮箱不能为空'),
            'email.email' => __('邮箱格式错误'),
        ]);

        if ($validator->fails()) {
            return $this->errorJson($validator->errors()->first());
        }
        
        $updateData = [
            'nickname' => $data['nickname'],
            'email' => $data['email'],
        ];
        if (!empty($data['avatar'])) {
            $updateData['avatar'] = $data['avatar'];
        }
        
        // 更新信息
        $adminid = app('larke.auth')->getId();
        $status = AdminModel::where('id', $adminid)
            ->update($updateData);
        if ($status === false) {
            return $this->errorJson(__('修改失败'));
        }
        
        return $this->successJson(__('修改成功'));
    }

    /**
     * 修改密码
     */
    public function changePasssword(Request $request)
    {
        // 密码长度错误
        $oldPassword = $request->get('oldPassword');
        if (strlen($oldPassword) != 32) {
            return $this->errorJson(__('旧密码错误'));
        }

        // 密码长度错误
        $newPassword = $request->get('newPassword');
        if (strlen($newPassword) != 32) {
            return $this->errorJson(__('新密码错误'));
        }

        $newPassword2 = $request->get('newPassword2');
        if (strlen($newPassword2) != 32) {
            return $this->errorJson(__('确认密码错误'));
        }

        if ($newPassword != $newPassword2) {
            return $this->errorJson(__('两次密码输入不一致'));
        }

        $adminid = app('larke.auth')->getId();
        $adminInfo = AdminModel::where('id', $adminid)
            ->first();
        if (empty($adminInfo)) {
            return $this->errorJson(__('帐号错误'));
        }
        
        $password2 = (new PasswordService())
            ->withSalt(config('larke.passport.salt'))
            ->encrypt($oldPassword, $adminInfo['passport_salt']); 
        if ($password2 != $adminInfo['password']) {
            return $this->errorJson(__('用户密码错误'));
        }

        // 新密码
        $newPasswordInfo = (new PasswordService())
            ->withSalt(config('larke.passport.salt'))
            ->encrypt($newPassword); 

        // 更新信息
        $status = AdminModel::where('id', $adminInfo['id'])
            ->update([
                'password' => $newPasswordInfo['password'],
                'passport_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            return $this->errorJson(__('密码修改失败'));
        }
        
        return $this->successJson(__('密码修改成功'));
    }

    /**
     * 我的菜单信息
     */
    public function menus(Request $request)
    {
        \Enforcer::addPermissionForUser('eve', 'articles', 'read');
    }

}
