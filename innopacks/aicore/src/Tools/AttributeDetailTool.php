<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Attribute;
use InvalidArgumentException;

class AttributeDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'attribute_detail';
    }

    public function description(): string
    {
        return 'Get full details of a single attribute by ID, including its values and group info.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id' => ['type' => 'integer', 'description' => 'Attribute ID'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'attributes_index';
    }

    public function execute(array $arguments): mixed
    {
        $attribute = Attribute::query()->with(['translation', 'group.translation', 'values.translation'])->find((int) ($arguments['id'] ?? 0));

        if (! $attribute) {
            throw new InvalidArgumentException("Attribute [{$arguments['id']}] not found.");
        }

        return [
            'id'                 => $attribute->id,
            'name'               => $attribute->translation->name ?? '',
            'attribute_group_id' => $attribute->attribute_group_id,
            'group_name'         => $attribute->group->translation->name ?? '',
            'category_id'        => $attribute->category_id,
            'position'           => $attribute->position,
            'values'             => $attribute->values->map(fn ($v) => [
                'id'   => $v->id,
                'name' => $v->translation->name ?? '',
            ])->values()->all(),
        ];
    }
}
