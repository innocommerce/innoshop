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
use InnoShop\Common\Repositories\AttributeRepo;
use InvalidArgumentException;

class AttributeUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'attribute_update';
    }

    public function description(): string
    {
        return 'Update an existing product attribute. Only provided fields are changed (PATCH semantics).';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id'                 => ['type' => 'integer', 'description' => 'Attribute ID'],
                'name'               => ['type' => 'string', 'description' => 'New attribute name (default locale)'],
                'attribute_group_id' => ['type' => 'integer', 'description' => 'New attribute group ID'],
                'category_id'        => ['type' => 'integer', 'description' => 'New category ID'],
                'position'           => ['type' => 'integer', 'description' => 'Display position'],
                'translations'       => ['type' => 'object', 'description' => 'Locale translations, e.g. {"zh-cn": {"locale": "zh-cn", "name": "材质"}}'],
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'attributes_update';
    }

    public function execute(array $arguments): mixed
    {
        $attribute = Attribute::query()->find((int) ($arguments['id'] ?? 0));
        if (! $attribute) {
            throw new InvalidArgumentException("Attribute [{$arguments['id']}] not found.");
        }

        $data = [];
        foreach (['attribute_group_id', 'category_id', 'position'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $data[$key] = $arguments[$key];
            }
        }

        if ($name = $arguments['name'] ?? '') {
            $data['translations'] = [
                ['locale' => locale_code(), 'name' => $name],
            ];
        }
        if ($extra = $arguments['translations'] ?? []) {
            foreach ((array) $extra as $locale => $fields) {
                if (is_array($fields) && ($fields['name'] ?? '')) {
                    $data['translations'][] = ['locale' => $locale, 'name' => $fields['name']];
                }
            }
        }

        if (empty($data)) {
            throw new InvalidArgumentException('No fields to update.');
        }

        AttributeRepo::getInstance()->update($attribute, $data);

        $attribute->refresh()->load('translation');

        return [
            'id'                 => $attribute->id,
            'name'               => $attribute->translation->name ?? '',
            'attribute_group_id' => $attribute->attribute_group_id,
        ];
    }
}
