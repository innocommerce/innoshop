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
        'name'      => 'bank_name',
        'label_key' => 'common.bank_name',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'bank_account',
        'label_key' => 'common.bank_account',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'bank_username',
        'label_key' => 'common.bank_username',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'bank_comment',
        'label_key' => 'common.bank_comment',
        'type'      => 'textarea',
        'required'  => false,
    ],
];
