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
use Illuminate\Support\Facades\Hash;
use InnoShop\Common\Models\Customer;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    protected static ?string $password;

    public function definition(): array
    {
        return [
            'email'             => fake()->unique()->safeEmail(),
            'password'          => static::$password ??= Hash::make('password'),
            'name'              => fake()->name(),
            'avatar'            => null,
            'customer_group_id' => null,
            'address_id'        => null,
            'locale'            => 'en',
            'active'            => true,
            'code'              => null,
            'from'              => Customer::FROM_PC_WEB,
            'calling_code'      => '+1',
            'telephone'         => fake()->phoneNumber(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function mobile(): static
    {
        return $this->state(fn (array $attributes) => [
            'from' => Customer::FROM_MOBILE_WEB,
        ]);
    }

    public function miniapp(): static
    {
        return $this->state(fn (array $attributes) => [
            'from' => Customer::FROM_MINIAPP,
        ]);
    }
}
