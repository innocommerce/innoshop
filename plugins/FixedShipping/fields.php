<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

return [
    [
        'name'      => 'type',
        'label_key' => 'common.type',
        'type'      => 'select',
        'options'   => [
            ['value' => 'fixed', 'label_key' => 'common.fixed'],
            ['value' => 'percent', 'label_key' => 'common.percent'],
        ],
        'required' => true,
        'rules'    => 'required',
    ],
    [
        'name'      => 'value',
        'label_key' => 'common.value',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
];
