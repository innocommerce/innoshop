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
        'name'      => 'address_erc20',
        'label_key' => 'common.address_erc20',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'address_trc20',
        'label_key' => 'common.address_trc20',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'address_bep20',
        'label_key' => 'common.address_bep20',
        'type'      => 'string',
        'required'  => true,
        'rules'     => 'required',
    ],
    [
        'name'      => 'comment',
        'label_key' => 'common.comment',
        'type'      => 'textarea',
        'required'  => false,
    ],
];
