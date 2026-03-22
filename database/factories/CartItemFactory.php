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
use Illuminate\Support\Str;
use InnoShop\Common\Models\CartItem;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Product;

/**
 * @extends Factory<CartItem>
 */
class CartItemFactory extends Factory
{
    protected $model = CartItem::class;

    public function definition(): array
    {
        return [
            'customer_id' => null,
            'product_id'  => Product::factory(),
            'sku_code'    => 'SKU-'.strtoupper(Str::random(8)),
            'guest_id'    => null,
            'selected'    => true,
            'quantity'    => fake()->numberBetween(1, 5),
            'item_type'   => null,
            'reference'   => null,
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

    public function forProduct(Product $product, string $skuCode): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'sku_code'   => $skuCode,
        ]);
    }

    public function unselected(): static
    {
        return $this->state(fn (array $attributes) => [
            'selected' => false,
        ]);
    }
}
