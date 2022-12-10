<?php

return [
    'title' => '{extensionTitle} 扩展',
    'url' => '#',
    'method' => 'OPTIONS',
    'slug' => $slug,
    'description' => '{extensionTitle} 扩展描述',
    'children' => [
        [
            'title' => '数据列表',
            'url' => '{extensionName}',
            'method' => 'GET',
            'slug' => 'larke-admin.ext.{extensionName}.index',
            'description' => '数据列表',
        ],
        [
            'title' => '数据详情',
            'url' => '{extensionName}/{id}',
            'method' => 'GET',
            'slug' => 'larke-admin.ext.{extensionName}.detail',
            'description' => '数据详情',
        ],
        [
            'title' => '删除数据',
            'url' => '{extensionName}/{id}',
            'method' => 'DELETE',
            'slug' => 'larke-admin.ext.{extensionName}.delete',
            'description' => '删除某条数据',
        ],
    ],
];
