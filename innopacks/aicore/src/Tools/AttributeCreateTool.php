<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\AttributeRepo;
use InvalidArgumentException;

class AttributeCreateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'attribute_create';
    }

    public function description(): string
    {
        return 'Create a new product attribute (technical parameter like material, weight) with translations and optional values.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'name'               => ['type' => 'string', 'description' => 'Attribute name (default locale), e.g. "Material", "Weight"'],
                'attribute_group_id' => ['type' => 'integer', 'description' => 'Attribute group ID, 0 for ungrouped'],
                'category_id'        => ['type' => 'integer', 'description' => 'Category ID, 0 for all categories'],
                'position'           => ['type' => 'integer', 'description' => 'Display position, default 0'],
                'values'             => ['type' => 'array', 'description' => 'Initial attribute values, e.g. [{"name": "Cotton", "translations": {"zh-cn": {"name": "棉"}}}]'],
                'translations'       => ['type' => 'object', 'description' => 'Additional locale translations, e.g. {"zh-cn": {"locale": "zh-cn", "name": "材质"}}'],
            ],
            'required' => ['name'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'attributes_create';
    }

    public function execute(array $arguments): mixed
    {
        $name = (string) ($arguments['name'] ?? '');
        if ($name === '') {
            throw new InvalidArgumentException('Attribute name is required.');
        }

        $data = [
            'attribute_group_id' => $arguments['attribute_group_id'] ?? 0,
            'category_id'        => $arguments['category_id'] ?? 0,
            'position'           => $arguments['position'] ?? 0,
            'translations'       => [],
        ];

        $data['translations'][] = ['locale' => locale_code(), 'name' => $name];
        if ($extra = $arguments['translations'] ?? []) {
            foreach ((array) $extra as $locale => $fields) {
                if (is_array($fields) && ($fields['name'] ?? '')) {
                    $data['translations'][] = ['locale' => $locale, 'name' => $fields['name']];
                }
            }
        }

        if ($values = $arguments['values'] ?? []) {
            $data['values'] = $values;
        }

        $attribute = AttributeRepo::getInstance()->create($data);

        return [
            'id'                 => $attribute->id,
            'name'               => $attribute->translation->name ?? '',
            'attribute_group_id' => $attribute->attribute_group_id,
        ];
    }
}
