<?php

declare (strict_types = 1);

namespace Larke\Admin\Command;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

/**
 * JWT 生成秘钥
 *
 * php artisan larke-admin:jwt-secret -f
 */
class JWTGenerateSecret extends Command
{
    /**
     * The console command signature.
     *
     * @var string
     */
    protected $signature = 'larke-admin:jwt-secret
        {--s|show : Display the key instead of modifying files.}
        {--always-no : Skip generating key if it already exists.}
        {--f|force : Skip confirmation when overwriting an existing key.}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the JWT secret key used to sign the tokens';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $key = base64_encode(Str::random(32));

        if ($this->option('show')) {
            $this->comment($key);

            return;
        }

        if (file_exists($path = $this->envPath()) === false) {
            return $this->displayKey($key);
        }

        if (Str::contains(file_get_contents($path), 'LARKE_ADMIN_JWT_PASSPHRASE') === false) {
            file_put_contents($path, PHP_EOL."LARKE_ADMIN_JWT_PASSPHRASE=$key".PHP_EOL, FILE_APPEND);
        } else {
            if ($this->option('always-no')) {
                $this->comment('Secret key already exists. Skipping...');

                return;
            }

            if ($this->isConfirmed() === false) {
                $this->comment('No changes were made to your secret key.');

                return;
            }

            file_put_contents($path, str_replace(
                'LARKE_ADMIN_JWT_PASSPHRASE='.$this->laravel['config']['larkeadmin.jwt.passphrase'],
                'LARKE_ADMIN_JWT_PASSPHRASE='.$key, 
                file_get_contents($path)
            ));
        }

        $this->displayKey($key);
    }

    /**
     * Display the key.
     *
     * @param  string  $key
     *
     * @return void
     */
    protected function displayKey($key)
    {
        $this->laravel['config']['larkeadmin.jwt.passphrase'] = $key;

        $this->info("jwt-auth secret [$key] set successfully.");
    }

    /**
     * Check if the modification is confirmed.
     *
     * @return bool
     */
    protected function isConfirmed()
    {
        return $this->option('force') ? true : $this->confirm(
            'This will invalidate all existing tokens. Are you sure you want to override the secret key?'
        );
    }

    /**
     * Get the .env file path.
     *
     * @return string
     */
    protected function envPath()
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }

        return $this->laravel->basePath('.env');
    }
}
