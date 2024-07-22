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

        return $user;
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
        $item->syncRoles($data['roles']);

        return $item;
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
            $data['roles'] = [];
        }

        return $data;
    }
}
