<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\CustomerRepo;
use InvalidArgumentException;

class CustomerUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'customer_update';
    }

    public function description(): string
    {
        return '⚠️ WRITE: Update a customer record. Only provided fields are changed (PATCH semantics). Does not change password or addresses.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id'     => ['type' => 'integer', 'description' => 'Customer ID'],
                'name'   => ['type' => 'string', 'description' => 'New customer name'],
                'email'  => ['type' => 'string', 'description' => 'New email address'],
                'active' => ['type' => 'boolean', 'description' => 'Active status'],
                'locale' => ['type' => 'string', 'description' => 'Preferred locale code, e.g. en, zh-cn'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'customers_update';
    }

    public function execute(array $arguments): mixed
    {
        $customer = Customer::query()->find((int) ($arguments['id'] ?? 0));
        if (! $customer) {
            throw new InvalidArgumentException("Customer [{$arguments['id']}] not found.");
        }

        $data = [];
        foreach (['name', 'email', 'active', 'locale'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $data[$key] = $arguments[$key];
            }
        }

        if (empty($data)) {
            throw new InvalidArgumentException('No fields to update.');
        }

        CustomerRepo::getInstance()->patch($customer, $data);

        $customer->refresh();

        return [
            'id'     => $customer->id,
            'name'   => $customer->name,
            'email'  => $customer->email,
            'active' => (bool) $customer->active,
            'locale' => $customer->locale,
        ];
    }
}
