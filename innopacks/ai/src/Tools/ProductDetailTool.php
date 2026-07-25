<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Tools;

use InnoShop\Common\Models\Product;
use InvalidArgumentException;

class ProductDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'product_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single product by ID, including SKUs and pricing.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Product ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_index';
    }

    public function execute(array $arguments): mixed
    {
        $id      = (int) ($arguments['id'] ?? 0);
        $product = Product::query()->with(['skus', 'translation'])->find($id);
        if (! $product) {
            throw new InvalidArgumentException("Product [{$id}] not found.");
        }

        return [
            'id'     => $product->id,
            'name'   => $product->translation->name ?? '',
            'slug'   => $product->slug,
            'price'  => $product->price,
            'active' => (bool) $product->active,
            'skus'   => $product->skus->map(fn ($sku) => [
                'code'       => $sku->code,
                'model'      => $sku->model,
                'price'      => $sku->price,
                'quantity'   => $sku->quantity,
                'is_default' => (bool) $sku->is_default,
            ])->values()->all(),
        ];
    }
}
