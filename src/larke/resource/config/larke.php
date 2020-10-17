<?php

return [

    'route' => [

        'prefix' => env('LARKE_ADMIN_ROUTE_PREFIX', 'admin'),

        'namespace' => 'Larke\\Admin\\Controller',

        'middleware' => ['web', 'admin'],
    ],
];
