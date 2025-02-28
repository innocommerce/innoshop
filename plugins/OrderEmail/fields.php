<?php

return [
    [
        'name'        => 'to_email',
        'label'       => '接收邮件的Email',
        'type'        => 'string',
        'required'    => false,
        'placeholder' => '',
    ],
    [
        'name'        => 'content',
        'label'       => '接收内容',
        'type'        => 'textarea',
        'required'    => false,
        'description' => '自定义接收内容,可写入部分动态内容(标识后，会被程序替换),订单号:{order:number},订单金额{order:total},订单时间:{order:created_at},订单中的产品名字及数量:{order:products_names}',
    ],
    [
        'name'    => 'send_node',
        'label'   => '发送节点',
        'type'    => 'select',
        'options' => [
            [
                'value' => '1',
                'label' => '仅创建订单',
            ],
            [
                'value' => '2',
                'label' => '仅支付订单',
            ],
            [
                'value' => '3',
                'label' => '创建和支付订单',
            ],
        ],
        'required'    => true,
        'description' => '控制在哪个节点发送邮件通知',
    ],
];
