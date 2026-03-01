<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Traits;

use Database\Factories\CustomerFactory;
use InnoShop\Common\Models\Customer;

trait CreatesCustomer
{
    protected function createCustomer(array $attributes = []): Customer
    {
        return CustomerFactory::new()->create($attributes);
    }

    protected function createInactiveCustomer(array $attributes = []): Customer
    {
        return CustomerFactory::new()->inactive()->create($attributes);
    }

    protected function createMobileCustomer(array $attributes = []): Customer
    {
        return CustomerFactory::new()->mobile()->create($attributes);
    }

    protected function actingAsCustomer(?Customer $customer = null): Customer
    {
        $customer = $customer ?? $this->createCustomer();
        $this->actingAs($customer, 'customer');

        return $customer;
    }
}
