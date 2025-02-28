<?php

return [

    [
        'name'    => 'must_login',
        'label'   => '强制登录发布',
        'type'    => 'select',
        'options' => [
            [
                'value' => '0',
                'label' => '关闭',
            ],
            [
                'value' => '1',
                'label' => '开启',
            ],
        ],
        'required'    => true,
        'description' => '开启后，用户必须登录后才可以发表问题和回复',
    ],
];
