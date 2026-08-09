<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Product\Sku;

class SkuAutocompleteTool extends BaseTool
{
    public function name(): string
    {
        return 'sku_autocomplete';
    }

    public function description(): string
    {
        return 'Quick SKU code search for autocomplete. Returns compact id+code list, useful for finding SKU details.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Search keyword matched against SKU code'],
                'limit'   => ['type' => 'integer', 'description' => 'Max results, default 10, max 25'],
            ],
            'required' => ['keyword'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_index';
    }

    public function execute(array $arguments): mixed
    {
        $keyword = (string) ($arguments['keyword'] ?? '');
        $limit   = min(25, max(1, (int) ($arguments['limit'] ?? 10)));

        $skus = Sku::query()->with('product.translation')
            ->where('code', 'like', "%{$keyword}%")
            ->limit($limit)
            ->get();

        return [
            'items' => $skus->map(fn ($sku) => [
                'id'           => $sku->id,
                'code'         => $sku->code,
                'product_id'   => $sku->product_id,
                'product_name' => $sku->product->translation->name ?? '',
                'price'        => $sku->price,
                'quantity'     => $sku->quantity,
            ])->values()->all(),
        ];
    }
}
