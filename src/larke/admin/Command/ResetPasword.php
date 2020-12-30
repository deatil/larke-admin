<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

use Larke\Admin\Support\Password as PasswordService;
use Larke\Admin\Model\Admin as AdminModel;

/**
 * 重置密码
 *
 * php artisan larke-admin:reset-pasword
 *
 */
class ResetPasword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:reset-pasword';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin reset-pasword';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        askForAdminName:
        $name = $this->ask('Please enter an adminName who needs to reset his password');
        
        $admin = AdminModel::query()
            ->where('name', $name)
            ->first();
        if (is_null($admin)) {
            $this->line("<error>The admin who you entered is not exists !</error> ");
            goto askForAdminName;
        }
        
        $newPassword = $this->secret('Please enter a password, not enter wiil rand make a new password');
        if (empty($newPassword)) {
            $newPassword = mt_rand(10000000, 99999999);
        }
        
        // 新密码
        $newPasswordInfo = (new PasswordService())
            ->withSalt(config('larkeadmin.passport.password_salt'))
            ->encrypt(md5($newPassword)); 

        // 更新信息
        $status = $admin->update([
                'password' => $newPasswordInfo['password'],
                'password_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            $this->line("<error>Reset password is error !</error> ");
            return;
        }
        
        $this->line("<info>Admin'newpassword is:</info> ".$newPassword);
    }
}
