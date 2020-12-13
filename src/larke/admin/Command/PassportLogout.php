<?php

namespace Larke\Admin\Command;

use Illuminate\Console\Command;

/**
 * 强制 jwt 的 refreshToken 放入黑名单
 *
 * php artisan larke-admin:passport-logout [refresh_token]
 *
 */
class PassportLogout extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'larke-admin:passport-logout {refresh_token}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'larke-admin passport-logout';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->logout();
    }
    
    /**
     * logout command.
     *
     * @return mixed
     */
    protected function logout()
    {
        $refreshToken = $this->argument('refresh_token');
        if (empty($refreshToken)) {
            $this->line("<error>Refresh_token is empty !</error> ");

            return;
        }
        
        if (app('larke.admin.cache')->has(md5($refreshToken))) {
            $this->line("<error>Refresh_token is logouted !</error> ");

            return;
        }
        
        $refreshJwt = app('larke.admin.jwt')
            ->withJti(config('larkeadmin.passport.refresh_token_id'))
            ->withToken($refreshToken)
            ->decode();
        
        if (!($refreshJwt->validate() && $refreshJwt->verify())) {
            $this->line("<error>Refresh_token'verify is error !</error> ");

            return;
        }
        
        $refreshAdminid = $refreshJwt->getClaim('adminid');
        if ($refreshAdminid === false) {
            $this->line("<error>Refresh_token'adminid is error !</error> ");

            return;
        }
        
        $refreshTokenExpiresIn = $refreshJwt->getClaim('exp') - $refreshJwt->getClaim('iat');
        
        // 添加缓存黑名单
        app('larke.admin.cache')->add(md5($refreshToken), $refreshToken, $refreshTokenExpiresIn);
        
        $this->line('<info>Logout success and adminid is:</info> '.$refreshAdminid);
    }
}
