<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\TaxRate;

class TaxRateListTool extends BaseTool
{
    public function name(): string
    {
        return 'tax_rate_list';
    }

    public function description(): string
    {
        return 'List tax rates with type, rate value, and region info. Supports filtering by tax class ID.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'tax_class_id' => ['type' => 'integer', 'description' => 'Filter by tax class ID'],
                'keyword'      => ['type' => 'string', 'description' => 'Search keyword matched against tax rate name'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'tax_rates_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = TaxRate::query()->with(['taxClass', 'region']);

        if ($taxClassId = $arguments['tax_class_id'] ?? 0) {
            $query->where('tax_class_id', (int) $taxClassId);
        }
        if ($keyword = $arguments['keyword'] ?? '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $taxRates = $query->orderBy('tax_class_id')->orderByDesc('id')->get();

        return [
            'total' => $taxRates->count(),
            'items' => $taxRates->map(fn ($tr) => [
                'id'             => $tr->id,
                'name'           => $tr->name,
                'type'           => $tr->type,
                'rate'           => $tr->rate,
                'tax_class_id'   => $tr->tax_class_id,
                'tax_class_name' => $tr->taxClass->name ?? '',
                'region_id'      => $tr->region_id,
                'region_name'    => $tr->region->name ?? '',
                'active'         => (bool) $tr->active,
            ])->values()->all(),
        ];
    }
}
