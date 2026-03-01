<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Traits;

use Database\Factories\AdminFactory;
use InnoShop\Common\Models\Admin;

trait CreatesAdmin
{
    protected function createAdmin(array $attributes = []): Admin
    {
        return AdminFactory::new()->create($attributes);
    }

    protected function createInactiveAdmin(array $attributes = []): Admin
    {
        return AdminFactory::new()->inactive()->create($attributes);
    }

    protected function actingAsAdmin(?Admin $admin = null): Admin
    {
        $admin = $admin ?? $this->createAdmin();
        $this->actingAs($admin, 'admin');

        return $admin;
    }
}
