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
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Product\Sku;

/**
 * @extends Factory<Sku>
 */
class ProductSkuFactory extends Factory
{
    protected $model = Sku::class;

    public function definition(): array
    {
        $price = fake()->randomFloat(2, 10, 1000);

        return [
            'product_id'   => Product::factory(),
            'images'       => [],
            'model'        => 'MODEL-'.strtoupper(Str::random(6)),
            'code'         => 'SKU-'.strtoupper(Str::random(8)),
            'price'        => $price,
            'origin_price' => $price * 1.2,
            'quantity'     => fake()->numberBetween(0, 100),
            'is_default'   => true,
            'position'     => 0,
            'variants'     => null,
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => 0,
        ]);
    }

    public function nonDefault(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_default' => false,
        ]);
    }

    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
        ]);
    }
}
