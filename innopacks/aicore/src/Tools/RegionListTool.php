<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Region;

class RegionListTool extends BaseTool
{
    public function name(): string
    {
        return 'region_list';
    }

    public function description(): string
    {
        return 'List regions (states/provinces) with optional filter by country ID. Data volume is small, returned in full.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'country_id' => ['type' => 'integer', 'description' => 'Filter by country ID'],
                'keyword'    => ['type' => 'string', 'description' => 'Search keyword matched against region name'],
                'active'     => ['type' => 'boolean', 'description' => 'Filter by active status'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'regions_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = Region::query();

        if ($countryId = $arguments['country_id'] ?? 0) {
            $query->where('country_id', (int) $countryId);
        }
        if ($keyword = $arguments['keyword'] ?? '') {
            $query->where('name', 'like', "%{$keyword}%");
        }
        if (array_key_exists('active', $arguments)) {
            $query->where('active', (bool) $arguments['active']);
        }

        $regions = $query->orderBy('country_id')->orderBy('name')->get();

        return [
            'total' => $regions->count(),
            'items' => $regions->map(fn ($r) => [
                'id'         => $r->id,
                'name'       => $r->name,
                'country_id' => $r->country_id,
                'code'       => $r->code,
                'active'     => (bool) $r->active,
            ])->values()->all(),
        ];
    }
}
