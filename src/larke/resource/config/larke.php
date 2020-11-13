<?php

return [
    'admin' => [
        'name' => "LarkeAdmin",
        'name_mini' => "Larke",
        'logo' => '<b>Larke</b> admin',
        'release' => 20201030,
        'version' => "1.0.1",
    ],
    
    'https' => env('LARKE_ADMIN_HTTPS', false),
    
    'route' => [
        'prefix' => env('LARKE_ADMIN_ROUTE_PREFIX', 'admin-api'),
        'namespace' => env('LARKE_ADMIN_ROUTE_NAMESPACE', 'Larke\\Admin\\Controller'),
        'middleware' => env('LARKE_ADMIN_ROUTE_MIDDLEWARE') ? explode(',', env('LARKE_ADMIN_ROUTE_MIDDLEWARE')) : ['larke.admin'],
        'admin_middleware' => env('LARKE_ADMIN_ROUTE_ADMIN_MIDDLEWARE') ? explode(',', env('LARKE_ADMIN_ROUTE_ADMIN_MIDDLEWARE')) : ['larke.admin.auth.admin'],
    ],
    
    'jwt' => [
        'alg' => env('LARKE_ADMIN_JWT_ALG', 'HS256'),
        'iss' => env('LARKE_ADMIN_JWT_ISS', 'admin-api.domain.com'),
        'aud' => env('LARKE_ADMIN_JWT_AUD', md5(request()->ip().request()->server('HTTP_USER_AGENT'))),
        'sub' => env('LARKE_ADMIN_JWT_SUB', 'larke-admin-passport'),
        'jti' => env('LARKE_ADMIN_JWT_JTI', 'larke-admin-jid'),
        'exp' => env('LARKE_ADMIN_JWT_EXP', 3600),
        'nbf' => env('LARKE_ADMIN_JWT_NBF', 0),
        
        'signer_type' => env('LARKE_ADMIN_JWT_SIGNER_TYPE', 'SECRECT'),
        'secrect' => env('LARKE_ADMIN_JWT_SECRECT', 's1fegdR'),
        'private_key' => env('LARKE_ADMIN_JWT_PRIVATE_KEY', ''),
        'public_key' => env('LARKE_ADMIN_JWT_PUBLIC_KEY', ''),
    ],
    
    'passport' => [
        'password_salt' => env('LARKE_ADMIN_PASSPORT_PASSWORD_SALT', 'e6c2ea864004a461e744b28a394df50c'),
        'header_captcha_key' => env('LARKE_ADMIN_PASSPORT_HEADER_CAPTCHA_KEY', 'Larke-Admin-Captcha-Id'),
        'access_token_id' => env('LARKE_ADMIN_PASSPORT_ACCESS_TOKEN_ID', 'larke-passport-access-token'),
        'access_expired_in' => env('LARKE_ADMIN_PASSPORT_ACCESS_EXPIRED_IN', 86400),
        'refresh_token_id' => env('LARKE_ADMIN_PASSPORT_REFRESH_TOKEN_ID', 'larke-passport-refresh-token'),
        'refresh_expired_in' => env('LARKE_ADMIN_PASSPORT_REFRESH_EXPIRED_IN', 604800),
    ],
    
    'cache' => [
        'store' => env('LARKE_ADMIN_CACHE_STORE', 'default'),
        
        'auth_rule' => [
            'store' => env('LARKE_ADMIN_CACHE_AUTH_RULE_STORE', 'default'),
            'key' => env('LARKE_ADMIN_CACHE_AUTH_RULE_KEY', md5('larke_no_auth_rule')),
            'ttl' => env('LARKE_ADMIN_CACHE_AUTH_RULE_TTL', 43200),
        ],
    ],
    
    'response' => [
        'json' => [
            'is_allow_origin' => env('LARKE_ADMIN_RESPONSE_JSON_IS_ALLOW_ORIGIN', 1),
            'allow_origin' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_ORIGIN', '*'),
            'allow_credentials' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_CREDENTIALS', 0),
            'max_age' => env('LARKE_ADMIN_RESPONSE_JSON_MAX_AGE', ''),
            'allow_methods' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_METHODS', 'GET,POST,PATCH,PUT,DELETE,OPTIONS'),
            'allow_headers' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_HEADERS', 'X-Requested-With,X_Requested_With,Content-Type'),
            'expose_headers' => env('LARKE_ADMIN_RESPONSE_JSON_EXPOSE_HEADERS', 'Larke-Admin-Captcha-Id'),
        ],
    ],
    
    'auth' => [
        'excepts' => env('LARKE_ADMIN_AUTH_EXCEPTS') ? explode(',', env('LARKE_ADMIN_AUTH_EXCEPTS')) : [],
        'admin_id' => env('LARKE_ADMIN_AUTH_ADMIN_ID', '04f65b19e5a2513fe5a89100309da9b7'),
    ],
    
    'extension' => [
        'directory' => env('LARKE_ADMIN_EXTENSION_DIRECTORY', base_path('extension')),
    ],
    
    'upload' => [
        // Disk in `config/filesystem.php`.
        'disk' => env('LARKE_ADMIN_UPLOAD_DISK', 'public'),
        
        'directory' => [
            'image' => env('LARKE_ADMIN_UPLOAD_DIRECTORY_IMAGE', 'images'),
            'media' => env('LARKE_ADMIN_UPLOAD_DIRECTORY_MEDIA', 'medias'),
            'file' => env('LARKE_ADMIN_UPLOAD_DIRECTORY_FILE', 'files'),
        ],
        
        'file_types' => [
            'image'  => '/^(gif|png|jpe?g|svg|webp)$/i',
            'html'   => '/^(htm|html)$/i',
            'office' => '/^(docx?|xlsx?|pptx?|pps|potx?)$/i',
            'gdocs'  => '/^(docx?|xlsx?|pptx?|pps|potx?|rtf|ods|odt|pages|ai|dxf|ttf|tiff?|wmf|e?ps)$/i',
            'text'   => '/^(txt|md|csv|nfo|ini|json|php|js|css|ts|sql)$/i',
            'video'  => '/^(og?|mp4|webm|mp?g|mov|3gp)$/i',
            'audio'  => '/^(og?|mp3|mp?g|wav)$/i',
            'pdf'    => '/^(pdf)$/i',
            'flash'  => '/^(swf)$/i',
        ],
    ],
];
