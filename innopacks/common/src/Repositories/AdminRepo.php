<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use InnoShop\Common\Models\Admin;
use Throwable;

class AdminRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/admin.name')],
            ['name' => 'email', 'type' => 'input', 'label' => trans('panel/admin.email')],
            ['name' => 'locale', 'type' => 'input', 'label' => trans('panel/admin.locale')],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'name', 'label' => trans('panel/admin.name')],
            ['value' => 'email', 'label' => trans('panel/admin.email')],
        ];

        return fire_hook_filter('common.repo.admin.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [
            [
                'name'    => 'active',
                'label'   => trans('panel/common.status'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => '1', 'label' => trans('panel/common.active_yes')],
                    ['value' => '0', 'label' => trans('panel/common.active_no')],
                ],
            ],
        ];

        return fire_hook_filter('common.repo.admin.filter_button_options', $filters);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $email = $data['email'] ?? '';
        $data  = $this->handleData($data);
        $user  = Admin::query()->where('email', $email)->first();
        if (empty($user)) {
            $user = new Admin;
        }

        $user->fill($data);
        $user->saveOrFail();
        $user->assignRole($data['roles']);

        return fire_hook_filter('common.repo.admin.create.after', $user);
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     */
    public function update(mixed $item, $data): mixed
    {
        $data = $this->handleData($data);
        $item->update($data);

        if (isset($data['roles'])) {
            $item->syncRoles($data['roles']);
        }

        return fire_hook_filter('common.repo.admin.update.after', $item);
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function handleData($data): mixed
    {
        $password = $data['password'] ?? '';
        if ($password) {
            $data['password'] = bcrypt($password);
        } else {
            unset($data['password']);
        }

        $roles = $data['roles'] ?? [];
        if ($roles) {
            $data['roles'] = collect($roles)->map(function ($item) {
                return (int) $item;
            });
        } else {
            unset($data['roles']);
        }

        return $data;
    }
}
