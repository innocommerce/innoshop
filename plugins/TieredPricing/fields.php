<?php

return [
    [
        'name'    => 'init_min_quantity',
        'label'   => '起拍数量',
        'type'    => 'select',
        'options' => [

            [
                'value' => '1',
                'label' => '默认为起批量',
            ],
            [
                'value' => '2',
                'label' => '默认为1',
            ],
        ],
        'emptyOption' => false,
        'required'    => true,
        'description' => '',
    ],
];
