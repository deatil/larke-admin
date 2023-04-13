## Eddsa 私钥公钥生成

```php
// Eddsa 密钥对生成
function cteate_eddsa_pair() {
    // $sign_seed = random_bytes(SODIUM_CRYPTO_SIGN_SEEDBYTES);
    // $sign_pair = sodium_crypto_sign_seed_keypair($sign_seed);

    $sign_pair = sodium_crypto_sign_keypair();
    
    $sign_secret = sodium_crypto_sign_secretkey($sign_pair);
    $sign_public = sodium_crypto_sign_publickey($sign_pair);
    
    return [base64_encode($sign_secret), base64_encode($sign_public)];
}
```

```php
$private_key = '...';
$binary_private_key = hex2bin(base64_decode($private_key));
$message = "Hello!";
$signature = sodium_crypto_sign_detached($message, $binary_private_key);

echo bin2hex($signature);
```
