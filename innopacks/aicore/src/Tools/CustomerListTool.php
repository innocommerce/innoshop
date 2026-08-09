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

class CustomerListTool extends BaseTool
{
    public function name(): string
    {
        return 'customer_list';
    }

    public function description(): string
    {
        return 'List customers with pagination. Supports keyword search on name or email.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword'  => ['type' => 'string', 'description' => 'Search keyword matched against customer name or email'],
                'email'    => ['type' => 'string', 'description' => 'Filter by email'],
                'active'   => ['type' => 'boolean', 'description' => 'Filter by active status; omit to include both'],
                'page'     => ['type' => 'integer', 'description' => 'Page number, default 1'],
                'per_page' => ['type' => 'integer', 'description' => 'Items per page, default 10, max 50'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'customers_index';
    }

    public function execute(array $arguments): mixed
    {
        $filters = [
            'keyword'  => $arguments['keyword'] ?? '',
            'page'     => max(1, (int) ($arguments['page'] ?? 1)),
            'per_page' => min(50, max(1, (int) ($arguments['per_page'] ?? 10))),
        ];

        if ($email = $arguments['email'] ?? '') {
            $filters['email'] = $email;
        }
        if (array_key_exists('active', $arguments)) {
            $filters['active'] = (bool) $arguments['active'];
        }

        $paginator = CustomerRepo::getInstance()->list($filters);

        return [
            'total' => $paginator->total(),
            'page'  => $paginator->currentPage(),
            'items' => $paginator->map(fn ($customer) => [
                'id'        => $customer->id,
                'name'      => $customer->name,
                'email'     => $customer->email,
                'telephone' => $customer->telephone,
                'locale'    => $customer->locale,
                'active'    => (bool) $customer->active,
                'from'      => $customer->from,
            ])->values()->all(),
        ];
    }
}
