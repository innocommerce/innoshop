<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Tools;

use InnoShop\Common\Models\Product\Sku;

class StockReportTool extends BaseTool
{
    public function name(): string
    {
        return 'stock_report';
    }

    public function description(): string
    {
        return 'Report SKUs at or below a stock threshold, useful for restocking decisions.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'threshold' => ['type' => 'integer', 'description' => 'Stock level at or below which SKUs are reported, default 10'],
                'limit'     => ['type' => 'integer', 'description' => 'Max SKUs returned, default 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'products_index';
    }

    public function execute(array $arguments): mixed
    {
        $threshold = (int) ($arguments['threshold'] ?? 10);
        $limit     = min(200, max(1, (int) ($arguments['limit'] ?? 50)));

        $skus = Sku::query()
            ->with('product.translation')
            ->where('quantity', '<=', $threshold)
            ->orderBy('quantity')
            ->limit($limit)
            ->get();

        return [
            'threshold' => $threshold,
            'count'     => $skus->count(),
            'items'     => $skus->map(fn ($sku) => [
                'product_id'   => $sku->product_id,
                'product_name' => $sku->product?->translation->name ?? '',
                'sku_code'     => $sku->code,
                'quantity'     => $sku->quantity,
            ])->values()->all(),
        ];
    }
}
