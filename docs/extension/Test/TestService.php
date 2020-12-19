<?php

namespace Test;

use Larke\Admin\Extension\Service;

class TestService extends Service
{
    public $info = [
        'name' => 'Test',
        'title' => '测试扩展',
        'introduce' => '测试扩展描述',
        'author' => 'deatil', 
        'authorsite' => 'http://github.com/deatil', // 选填
        'authoremail' => 'deatil@github.com',
        'version' => '1.0.3', // 扩展版本
        'adaptation' => '1.0.*', // 适配admin系统版本
        'require_extension' => [], // 选填
        'config' => [ // 配置，选填
            [
                'name' => 'atext',
                'title' => '文本',
                'type' => 'text',
                'value' => '',
                'require' => '1',
                'description' => '设置内容文本',
            ],
            [
                'name' => 'atextarea',
                'title' => '文本框',
                'type' => 'textarea',
                'value' => '',
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
    
    public function boot()
    {
        $this->commands([
            Command\Test::class,
        ]);
    }
}
