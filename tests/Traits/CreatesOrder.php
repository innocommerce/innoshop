<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Tests\Traits;

use Database\Factories\OrderFactory;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;

trait CreatesOrder
{
    protected function createOrder(array $attributes = []): Order
    {
        return OrderFactory::new()->create($attributes);
    }

    protected function createOrderForCustomer(Customer $customer, array $attributes = []): Order
    {
        return OrderFactory::new()->forCustomer($customer)->create($attributes);
    }

    protected function createUnpaidOrder(array $attributes = []): Order
    {
        return OrderFactory::new()->unpaid()->create($attributes);
    }

    protected function createPaidOrder(array $attributes = []): Order
    {
        return OrderFactory::new()->paid()->create($attributes);
    }

    protected function createShippedOrder(array $attributes = []): Order
    {
        return OrderFactory::new()->shipped()->create($attributes);
    }

    protected function createCompletedOrder(array $attributes = []): Order
    {
        return OrderFactory::new()->completed()->create($attributes);
    }

    protected function createCancelledOrder(array $attributes = []): Order
    {
        return OrderFactory::new()->cancelled()->create($attributes);
    }
}
