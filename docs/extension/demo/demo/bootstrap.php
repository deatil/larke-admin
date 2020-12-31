<?php

use Larke\Admin\Facade\Extension;

if (! class_exists("Larke\\Admin\\Demo\\ServiceProvider")) {
    Extension::namespaces([
        'Larke\\Admin\\Demo\\' => __DIR__ . '/src',
    ]);
}

Extension::extend('demo/demo', Larke\Admin\Demo\ServiceProvider::class);
