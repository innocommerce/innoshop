<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\CustomerRepo;

class CustomerAutocompleteTool extends BaseTool
{
    public function name(): string
    {
        return 'customer_autocomplete';
    }

    public function description(): string
    {
        return 'Quick customer search by name or email for autocomplete. Returns compact id+name+email list, useful for finding customer IDs.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Search keyword matched against customer name or email'],
                'limit'   => ['type' => 'integer', 'description' => 'Max results, default 10, max 25'],
            ],
            'required' => ['keyword'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'customers_index';
    }

    public function execute(array $arguments): mixed
    {
        $keyword = (string) ($arguments['keyword'] ?? '');
        $limit   = min(25, max(1, (int) ($arguments['limit'] ?? 10)));

        $customers = CustomerRepo::getInstance()->autocomplete($keyword, $limit);

        return [
            'items' => $customers->map(fn ($c) => [
                'id'        => $c->id,
                'name'      => $c->name,
                'email'     => $c->email,
                'telephone' => $c->telephone,
            ])->values()->all(),
        ];
    }
}
