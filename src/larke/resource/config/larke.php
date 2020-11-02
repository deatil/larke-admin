<?php

return [
    'admin' => [
        'name' => "LarkeAdmin",
        'name_mini' => "Larke",
        'logo' => '<b>Larke</b> admin',
        'release' => 20201030,
        'version' => "1.0.0",
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
        'password_salt' => env('LARKE_ADMIN_PASSPORT_PASSWORD_SALT', 'd,d7ja0db1a974;38cE84976abbac2cd'),
        'access_token_id' => env('LARKE_ADMIN_PASSPORT_ACCESS_TOKEN_ID', 'larke-passport-access-token'),
        'access_expired_in' => env('LARKE_ADMIN_PASSPORT_ACCESS_EXPIRED_IN', 86400),
        'refresh_token_id' => env('LARKE_ADMIN_PASSPORT_REFRESH_TOKEN_ID', 'larke-passport-refresh-token'),
        'refresh_expired_in' => env('LARKE_ADMIN_PASSPORT_REFRESH_EXPIRED_IN', 604800),
    ],
    
    'cache' => [
        'store' => env('LARKE_ADMIN_CACHE_STORE', 'default'),
    ],
    
    'response' => [
        'json' => [
            'is_allow_origin' => env('LARKE_ADMIN_RESPONSE_JSON_IS_ALLOW_ORIGIN', 0),
            'allow_origin' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_ORIGIN', '*'),
            'allow_credentials' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_CREDENTIALS', 0),
            'max_age' => env('LARKE_ADMIN_RESPONSE_JSON_MAX_AGE', ''),
            'allow_methods' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_METHODS', 'GET,POST,PATCH,PUT,DELETE,OPTIONS'),
            'allow_headers' => env('LARKE_ADMIN_RESPONSE_JSON_ALLOW_HEADERS', 'X-Requested-With,X_Requested_With,Content-Type'),
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
