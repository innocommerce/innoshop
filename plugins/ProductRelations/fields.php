<?php

return [
    [
        'name'        => 'relations_limit',
        'label'       => '关联商品数量',
        'type'        => 'string',
        'required'    => false,
        'placeholder' => '请填写数量',
        'description' => '默认为4个商品。',
    ],
    [
        'name'    => 'relations_type',
        'label'   => '关联方式',
        'type'    => 'select',
        'options' => [
            [
                'value' => '1',
                'label' => '全局随机关联',
            ],
            [
                'value' => '2',
                'label' => '同分类随机关联,无分类则不显示',
            ],
            [
                'value' => '3',
                'label' => '同分类随机关联,无分类则全局关联',
            ],
        ],
        'required' => true,
    ],
    [
        'name'    => 'cache_status',
        'label'   => '缓存',
        'type'    => 'select',
        'options' => [
            [
                'value' => '1',
                'label' => '开启',
            ],
            [
                'value' => '0',
                'label' => '关闭',
            ],
        ],
        'required'    => true,
        'description' => '开启缓存后，生成的关联数据在每次用户请求时都会缓存1分钟，减少查询数据库的次数',
    ],

];
