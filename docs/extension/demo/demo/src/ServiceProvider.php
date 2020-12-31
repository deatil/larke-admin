<?php

namespace Larke\Admin\Demo;

use Larke\Admin\Extension\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public $info = [
        'title' => '示例扩展',
        'description' => '示例扩展描述',
        'keywords' => [
            'Demo',
            'Larke',
            'Admin',
            'LarkeAdmin',
        ],
        'homepage' => 'http://github.com/deatil',
        'authors' => [
            [
                'name' => 'deatil', 
                'email' => 'deatil@github.com', 
                'homepage' => 'http://github.com/deatil', 
            ],
        ],
        'version' => '1.0.2',
        'adaptation' => '1.1.*',
        'require' => [
            // 'SignCert' => '1.0.*'
        ], // 选填
        'config' => [ // 配置，选填
            [
                'name' => 'atext',
                'title' => '文本',
                'type' => 'text',
                'value' => '文本',
                'require' => '1',
                'description' => '设置内容文本',
            ],
            [
                'name' => 'atextarea',
                'title' => '文本框',
                'type' => 'textarea',
                'value' => '文本框',
                'require' => '1',
                'description' => '设置内容文本框',
            ],
            [
                'name' => 'aradio',
                'title' => '单选',
                'type' => 'radio',
                'options' => [
                    '1' => '单选1',
                    '2' => '单选2',
                    '3' => '单选3',
                ],
                'value' => '1',
                'require' => '1',
                'description' => '设置内容单选',
            ],
            [
                'name' => 'acheckbox',
                'title' => '多选',
                'type' => 'checkbox',
                'options' => [
                    '1' => '多选1',
                    '2' => '多选2',
                    '3' => '多选3',
                ],
                'value' => '1',
                'require' => '1',
                'description' => '设置内容多选',
            ],
            [
                'name' => 'aselect',
                'title' => '下拉',
                'type' => 'select',
                'options' => [
                    '1' => '下拉1',
                    '2' => '下拉2',
                    '3' => '下拉3',
                ],
                'value' => '1',
                'require' => '1', // 1-必填
                'description' => '设置内容下拉',
            ],
            [
                'name' => 'aswitch',
                'title' => '开关',
                'type' => 'switch',
                'value' => '1',
                'require' => '1',
                'description' => '设置内容开关',
            ],
        ], 
    ];
    
    public function start()
    {
        $this->commands([
            Command\Demo::class,
        ]);
    }
}
