<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\TaxClass;

class TaxClassListTool extends BaseTool
{
    public function name(): string
    {
        return 'tax_class_list';
    }

    public function description(): string
    {
        return 'List tax classes (tax categories) with their names and descriptions.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'keyword' => ['type' => 'string', 'description' => 'Search keyword matched against tax class name'],
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'tax_classes_index';
    }

    public function execute(array $arguments): mixed
    {
        $query = TaxClass::query();

        if ($keyword = $arguments['keyword'] ?? '') {
            $query->where('name', 'like', "%{$keyword}%");
        }

        $classes = $query->orderBy('name')->get();

        return [
            'total' => $classes->count(),
            'items' => $classes->map(fn ($tc) => [
                'id'          => $tc->id,
                'name'        => $tc->name,
                'description' => $tc->description,
                'active'      => (bool) $tc->active,
            ])->values()->all(),
        ];
    }
}
