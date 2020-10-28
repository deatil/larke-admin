<?php

return [
    'route' => [
        'prefix' => env('LARKE_ADMIN_ROUTE_PREFIX', 'admin-api'),
        'namespace' => env('LARKE_ADMIN_ROUTE_NAMESPACE', 'Larke\\Admin\\Controller'),
        'middleware' => env('LARKE_ADMIN_ROUTE_MIDDLEWARE') ? explode(',', env('LARKE_ADMIN_ROUTE_MIDDLEWARE')) : ['larke.admin'],
    ],
    'jwt' => [
        'alg' => env('LARKE_ADMIN_JWT_ALG', 'HS256'),
        'iss' => env('LARKE_ADMIN_JWT_ISS', 'api.larke_admin.com'),
        'aud' => env('LARKE_ADMIN_JWT_AUD', 'larke_admin'),
        'sub' => env('LARKE_ADMIN_JWT_SUB', 'larke_admin'),
        'jti' => env('LARKE_ADMIN_JWT_JTI', 'larke_admin'),
        'exptime' => env('LARKE_ADMIN_JWT_EXPTIME', 3600),
        'notbeforetime' => env('LARKE_ADMIN_JWT_NOTBEFORETIME', 10),
        
        'signer_type' => env('LARKE_ADMIN_JWT_SIGNER_TYPE', 'SECRECT'),
        'secrect' => env('LARKE_ADMIN_JWT_SECRECT', 's1fegdR'),
        'private_key' => env('LARKE_ADMIN_JWT_PRIVATE_KEY', ''),
        'public_key' => env('LARKE_ADMIN_JWT_PUBLIC_KEY', ''),
    ],
    'passport' => [
        'salt' => env('LARKE_ADMIN_PASSPORT_SALT', 'd,d7ja0db1a974;38cE84976abbac2cd'),
        'expired_in' => env('LARKE_ADMIN_PASSPORT_EXPIRED_IN', 86400),
        'refresh_expired_in' => env('LARKE_ADMIN_PASSPORT_REFRESH_EXPIRED_IN', 604800),
        'access_token_id' => env('LARKE_ADMIN_PASSPORT_ACCESS_TOKEN_ID', 'larke-passport-access-token'),
        'refresh_token_id' => env('LARKE_ADMIN_PASSPORT_REFRESH_TOKEN_ID', 'larke-passport-refresh-token'),
    ],
    'cache' => [
        'store' => env('LARKE_ADMIN_CACHE_STORE', 'default'),
    ],
    'auth' => [
        'excepts' => env('LARKE_ADMIN_AUTH_EXCEPTS') ? explode(',', env('LARKE_ADMIN_AUTH_EXCEPTS')) : [],
        'admin_id' => env('LARKE_ADMIN_AUTH_ADMIN_ID', '04f65b19e5a2513fe5a89100309da9b7'),
    ],
];
