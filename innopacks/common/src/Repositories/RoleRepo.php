<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleRepo
{
    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @return mixed
     */
    public function list(): mixed
    {
        return Role::query()->paginate();
    }

    /**
     * Create role for admin users.
     *
     * @param  $data
     * @return Role
     * @throws Exception
     */
    public function create($data): Role
    {
        $adminRole = Role::findOrCreate($data['name'], 'admin');
        $this->syncPermissions($adminRole, $data['permissions']);

        return $adminRole;
    }

    /**
     * Edit admin role.
     *
     * @param  $item
     * @param  $data
     * @return Role
     * @throws Exception
     */
    public function update($item, $data): Role
    {
        $item->update([
            'name'       => $data['name'],
            'guard_name' => 'admin',
        ]);

        $this->syncPermissions($item, $data['permissions']);

        return $item;
    }

    /**
     * Sync all admin permissions.
     *
     * @param  Role  $adminRole
     * @param  $permissions
     * @throws Exception
     */
    private function syncPermissions(Role $adminRole, $permissions): void
    {
        $items = [];
        foreach ($permissions as $permission) {
            $code = $permission;
            Permission::findOrCreate($code);
            $items[] = $code;
        }

        if (empty($items)) {
            throw new Exception(panel_trans('admin.select_one_role'));
        }
        $adminRole->syncPermissions($items);
    }

    /**
     * Delete admin role.
     *
     * @param  $item
     */
    public function destroy($item): void
    {
        $item->delete();
    }
}
