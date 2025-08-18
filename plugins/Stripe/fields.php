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
        'name'      => 'publishable_key',
        'label_key' => 'common.publishable_key',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|min:32',
    ],
    [
        'name'      => 'secret_key',
        'label_key' => 'common.secret_key',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required|min:32',
    ],
    [
        'name'      => 'webhook_secret',
        'label_key' => 'common.webhook_secret',
        'type'      => 'string',
        'required'  => false,
    ],
    [
        'name'      => 'test_mode',
        'label_key' => 'common.test_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => '1', 'label_key' => 'common.enabled'],
            ['value' => '0', 'label_key' => 'common.disabled'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
    [
        'name'      => 'payment_mode',
        'label_key' => 'common.payment_mode',
        'type'      => 'select',
        'options'   => [
            ['value' => 'elements', 'label_key' => 'common.on_site_payment'],
            ['value' => 'checkout', 'label_key' => 'common.redirect_payment'],
        ],
        'required'    => true,
        'emptyOption' => false,
    ],
];
