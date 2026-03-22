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
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Product;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->words(3, true);

        return [
            'type'         => Product::TYPE_NORMAL,
            'brand_id'     => null,
            'images'       => [],
            'hover_image'  => null,
            'video'        => null,
            'price'        => fake()->randomFloat(2, 10, 1000),
            'tax_class_id' => null,
            'spu_code'     => 'SPU-'.strtoupper(Str::random(8)),
            'slug'         => Str::slug($name).'-'.Str::random(5),
            'is_virtual'   => false,
            'variables'    => null,
            'position'     => fake()->numberBetween(0, 100),
            'active'       => true,
            'weight'       => fake()->randomFloat(2, 0.1, 10),
            'weight_class' => 'kg',
            'sales'        => 0,
            'viewed'       => 0,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'active' => false,
        ]);
    }

    public function virtual(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_virtual' => true,
            'weight'     => 0,
        ]);
    }

    public function bundle(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => Product::TYPE_BUNDLE,
        ]);
    }

    public function withBrand(Brand $brand): static
    {
        return $this->state(fn (array $attributes) => [
            'brand_id' => $brand->id,
        ]);
    }
}
