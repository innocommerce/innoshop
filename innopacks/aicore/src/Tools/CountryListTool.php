<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Country;

class CountryListTool extends BaseTool
{
    public function name(): string
    {
        return 'country_list';
    }

    public function description(): string
    {
        return 'List all countries with code, name, and continent. Data volume is small, returned in full.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'continent' => ['type' => 'string', 'description' => 'Filter by continent name'],
                'keyword'   => ['type' => 'string', 'description' => 'Search keyword matched against country name or code'],
                'active'    => ['type' => 'boolean', 'description' => 'Filter by active status'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'countries_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = Country::query();

        if ($continent = $arguments['continent'] ?? '') {
            $query->where('continent', $continent);
        }
        if ($keyword = $arguments['keyword'] ?? '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }
        if (array_key_exists('active', $arguments)) {
            $query->where('active', (bool) $arguments['active']);
        }

        $countries = $query->orderBy('name')->get();

        return [
            'total' => $countries->count(),
            'items' => $countries->map(fn ($c) => [
                'id'        => $c->id,
                'name'      => $c->name,
                'code'      => $c->code,
                'continent' => $c->continent,
                'active'    => (bool) $c->active,
            ])->values()->all(),
        ];
    }
}
