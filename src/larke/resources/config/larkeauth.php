<?php

return [
    /**
     * Default larkeauth driver
     */
    'default' => 'larke',
    
    /**
     * Driver list
     */
    'guards' => [
        // larke-admin 决策器
        'larke' => [
            /**
             * Casbin model setting.
             */
            'model' => [
                // Available Settings: "file", "text"
                'config_type' => 'file',

                'config_file_path' => config_path('larkeauth-rbac-model.conf'),

                'config_text' => '',
            ],

            /*
             * Casbin adapter .
             */
            'adapter' => Larke\Auth\Adapters\DatabaseAdapter::class,

            /*
             * Database setting.
             */
            'database' => [
                // Database connection for following tables.
                'connection' => '',

                // Rule table name.
                'rules_table' => 'larke_rules',

                // Rule Model observer.
                'model_observer' => Larke\Admin\Observer\Rule::class,
            ],

            'log' => [
                // changes whether larkeauth will log messages to the Logger.
                'enabled' => false,

                // Casbin Logger, Supported: \Psr\Log\LoggerInterface|string
                'logger' => 'log',
            ],

            'cache' => [
                // changes larkeauth will cache the rules.
                'enabled' => false,

                // cache store
                'store' => 'default',

                // cache Key
                'key' => 'larke_rules',

                // ttl \DateTimeInterface|\DateInterval|int|null
                'ttl' => 24 * 60,
            ],
        ],
    ],

];
