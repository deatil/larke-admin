<?php

return [
    'passport' => [
        'salt' => env('LARKE_ADMIN_SALT', 'd,d7ja0db1a974;38cE84976abbac2cd'),
        'expired_in' => env('LARKE_ADMIN_EXPIRED_IN', 86400),
        'refresh_expired_in' => env('LARKE_ADMIN_REFRESH_EXPIRED_IN', 604800),
    ],
    'route' => [
        'prefix' => env('LARKE_ADMIN_ROUTE_PREFIX', 'admin-api'),
        'namespace' => env('LARKE_ADMIN_ROUTE_NAMESPACE', 'Larke\\Admin\\Controller'),
        'middleware' => env('LARKE_ADMIN_ROUTE_MIDDLEWARE') ? explode(',', env('LARKE_ADMIN_ROUTE_MIDDLEWARE')) : ['admin'],
    ],
    'jwt' => [
        'alg' => env('LARKE_ADMIN_JWT_ALG', 'HS256'),
        'iss' => env('LARKE_ADMIN_JWT_ISS', 'api.xxx.com'),
        'aud' => env('LARKE_ADMIN_JWT_AUD', 'larke_admin'),
        'sub' => env('LARKE_ADMIN_JWT_SUB', 'larke_admin'),
        'jti' => env('LARKE_ADMIN_JWT_JTI', 'sdwert5g'),
        'exptime' => env('LARKE_ADMIN_JWT_EXPTIME', '3600'),
        'notbeforetime' => env('LARKE_ADMIN_JWT_NOTBEFORETIME', '10'),
        
        'signer_type' => env('LARKE_ADMIN_JWT_SIGNER_TYPE', 'SECRECT'),
        'secrect' => env('LARKE_ADMIN_JWT_SECRECT', 's1fegdR'),
        'private_key' => env('LARKE_ADMIN_JWT_PRIVATE_KEY', ''),
        'public_key' => env('LARKE_ADMIN_JWT_PUBLIC_KEY', ''),
    ],
];
