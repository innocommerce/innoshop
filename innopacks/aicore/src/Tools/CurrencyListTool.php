<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Currency;

class CurrencyListTool extends BaseTool
{
    public function name(): string
    {
        return 'currency_list';
    }

    public function description(): string
    {
        return 'List all currencies with code, symbol, exchange rate, and active status.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'active' => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'currencies_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = Currency::query();

        if (array_key_exists('active', $arguments)) {
            $query->where('active', (bool) $arguments['active']);
        }

        $currencies = $query->orderByDesc('active')->orderBy('code')->get();

        return [
            'total' => $currencies->count(),
            'items' => $currencies->map(fn ($c) => [
                'id'            => $c->id,
                'name'          => $c->name,
                'code'          => $c->code,
                'symbol_left'   => $c->symbol_left,
                'symbol_right'  => $c->symbol_right,
                'value'         => $c->value,
                'decimal_place' => $c->decimal_place,
                'active'        => (bool) $c->active,
            ])->values()->all(),
        ];
    }
}
