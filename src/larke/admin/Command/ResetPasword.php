<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

use Larke\Admin\Service\Password as PasswordService;
use Larke\Admin\Model\Admin as AdminModel;

/**
 * 重置密码
 *
 * php artisan larke-admin:reset-pasword [id]
 *
 */
class ResetPasword extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:reset-pasword {id}';

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
        $id = $this->argument('id');
        if (empty($id)) {
            $this->line("<error>Admin'id is empty !</error> ");

            return;
        }
        
        $newPassword = mt_rand(10000000, 99999999);
        
        // 新密码
        $newPasswordInfo = (new PasswordService())
            ->withSalt(config('larke.passport.salt'))
            ->encrypt(md5($newPassword)); 

        // 更新信息
        $status = AdminModel::where('id', $id)
            ->update([
                'password' => $newPasswordInfo['password'],
                'passport_salt' => $newPasswordInfo['encrypt'],
            ]);
        if ($status === false) {
            $this->line("<error>Reset password is error !</error> ");

            return;
        }
        
        $this->line("<info>Admin'newpassword is:</info> ".$newPassword);
    }
}
