<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Spatie\Permission\Models\Role;

$options = [];
$roles   = \Spatie\Permission\Models\Role::query()->get();
foreach ($roles as $role) {
    $options[] = [
        'value' => $role->id,
        'label' => $role->name,
    ];
}

$adminOptions = [];
$salesRoleID  = plugin_setting('inquiry_quote', 'salesman_role_id');

if ($salesRoleID) {
    $salesRole = Role::query()->find($salesRoleID);
    if ($salesRole) {
        foreach ($salesRole->users as $admin) {
            $adminOptions[] = [
                'value' => $admin->id,
                'label' => $admin->name,
            ];
        }
    }
}

return [
    [
        'name'  => 'based_seller',
        'label' => '面向商家',
        'type'  => 'bool',
    ],
    [
        'name'  => 'based_salesman',
        'label' => '面向业务员',
        'type'  => 'bool',
    ],
    [
        'name'        => 'salesman_role_id',
        'label'       => '业务员角色',
        'type'        => 'select',
        'options'     => $options,
        'emptyOption' => false,
        'required'    => true,
        'rules'       => 'required',
    ],
    [
        'name'        => 'salesman_admin_id',
        'label'       => '默认业务员',
        'type'        => 'select',
        'options'     => $adminOptions,
        'emptyOption' => false,
        'required'    => true,
    ],
];
