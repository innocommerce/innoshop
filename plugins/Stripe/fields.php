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
        'name'     => 'publishable_key',
        'label'    => 'Publish Key',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|min:32',
    ],
    [
        'name'     => 'secret_key',
        'label'    => 'Secret Key',
        'type'     => 'string',
        'required' => true,
        'rules'    => 'required|min:32',
    ],
    [
        'name'    => 'test_mode',
        'label'   => 'Test Mode',
        'type'    => 'select',
        'options' => [
            ['value' => '1', 'label' => 'Enabled'],
            ['value' => '0', 'label' => 'Disabled'],
        ],
        'required' => true,
    ],
];
