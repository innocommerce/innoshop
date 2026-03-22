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
use InnoShop\Common\Models\Address;
use InnoShop\Common\Models\Country;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\State;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'customer_id' => null,
            'guest_id'    => null,
            'name'        => fake()->name(),
            'email'       => fake()->safeEmail(),
            'phone'       => fake()->phoneNumber(),
            'country_id'  => null,
            'state_id'    => null,
            'state'       => fake()->state(),
            'city_id'     => null,
            'city'        => fake()->city(),
            'zipcode'     => fake()->postcode(),
            'address_1'   => fake()->streetAddress(),
            'address_2'   => fake()->optional()->secondaryAddress(),
        ];
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }

    public function forGuest(string $guestId): static
    {
        return $this->state(fn (array $attributes) => [
            'guest_id' => $guestId,
        ]);
    }

    public function inCountry(Country $country): static
    {
        return $this->state(fn (array $attributes) => [
            'country_id' => $country->id,
        ]);
    }

    public function inState(State $state): static
    {
        return $this->state(fn (array $attributes) => [
            'state_id' => $state->id,
            'state'    => $state->name,
        ]);
    }
}
