<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Order;
use InnoShop\Common\Services\StateMachineService;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'number'                 => 'ORD-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -6)),
            'customer_id'            => null,
            'customer_group_id'      => null,
            'shipping_address_id'    => null,
            'billing_address_id'     => null,
            'customer_name'          => fake()->name(),
            'email'                  => fake()->safeEmail(),
            'calling_code'           => '+1',
            'telephone'              => fake()->phoneNumber(),
            'total'                  => fake()->randomFloat(2, 50, 500),
            'locale'                 => 'en',
            'currency_code'          => 'USD',
            'currency_value'         => 1.0,
            'ip'                     => fake()->ipv4(),
            'user_agent'             => fake()->userAgent(),
            'status'                 => StateMachineService::UNPAID,
            'shipping_method_code'   => 'flat_rate',
            'shipping_method_name'   => 'Flat Rate',
            'shipping_customer_name' => fake()->name(),
            'shipping_calling_code'  => '+1',
            'shipping_telephone'     => fake()->phoneNumber(),
            'shipping_country'       => fake()->country(),
            'shipping_country_id'    => null,
            'shipping_state_id'      => null,
            'shipping_state'         => fake()->state(),
            'shipping_city'          => fake()->city(),
            'shipping_address_1'     => fake()->streetAddress(),
            'shipping_address_2'     => null,
            'shipping_zipcode'       => fake()->postcode(),
            'billing_method_code'    => null,
            'billing_method_name'    => null,
            'billing_customer_name'  => null,
            'billing_calling_code'   => null,
            'billing_telephone'      => null,
            'billing_country'        => null,
            'billing_country_id'     => null,
            'billing_state_id'       => null,
            'billing_state'          => null,
            'billing_city'           => null,
            'billing_address_1'      => null,
            'billing_address_2'      => null,
            'billing_zipcode'        => null,
            'comment'                => null,
            'admin_note'             => null,
        ];
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id'       => $customer->id,
            'customer_group_id' => $customer->customer_group_id,
            'customer_name'     => $customer->name,
            'email'             => $customer->email,
            'calling_code'      => $customer->calling_code,
            'telephone'         => $customer->telephone,
        ]);
    }

    public function unpaid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StateMachineService::UNPAID,
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StateMachineService::PAID,
        ]);
    }

    public function shipped(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StateMachineService::SHIPPED,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StateMachineService::COMPLETED,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => StateMachineService::CANCELLED,
        ]);
    }
}
