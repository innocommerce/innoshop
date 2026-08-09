<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Category;
use InnoShop\Common\Repositories\CategoryRepo;
use InvalidArgumentException;

class CategoryUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'category_update';
    }

    public function description(): string
    {
        return 'Update an existing product category. Only provided fields are changed (PATCH semantics).';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id'               => ['type' => 'integer', 'description' => 'Category ID'],
                'name'             => ['type' => 'string', 'description' => 'New category name (default locale)'],
                'slug'             => ['type' => 'string', 'description' => 'New URL slug'],
                'parent_id'        => ['type' => 'integer', 'description' => 'New parent category ID'],
                'position'         => ['type' => 'integer', 'description' => 'Display position'],
                'active'           => ['type' => 'boolean', 'description' => 'Active status'],
                'meta_title'       => ['type' => 'string', 'description' => 'SEO meta title (default locale)'],
                'meta_description' => ['type' => 'string', 'description' => 'SEO meta description (default locale)'],
                'meta_keywords'    => ['type' => 'string', 'description' => 'SEO meta keywords, comma separated (default locale)'],
                'translations'     => self::translationsSchema([
                    'name'             => 'Category name in this locale. Required when adding a new locale.',
                    'meta_title'       => 'SEO meta title in this locale',
                    'meta_description' => 'SEO meta description in this locale',
                    'meta_keywords'    => 'SEO meta keywords, comma separated, in this locale',
                ], 'Per-locale overrides keyed by locale code (multilingual), e.g. {"zh-cn": {"name": "分类名", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Adding a new locale requires name. Same-locale entries override top-level fields.'),
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'categories_update';
    }

    public function execute(array $arguments): mixed
    {
        $category = Category::query()->find((int) ($arguments['id'] ?? 0));
        if (! $category) {
            throw new InvalidArgumentException("Category [{$arguments['id']}] not found.");
        }

        $data = [];
        foreach (['slug', 'position', 'active', 'parent_id'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $data[$key] = $arguments[$key];
            }
        }

        // Default-locale translation fields (name + SEO TDK)
        $fields = [];
        if ($name = $arguments['name'] ?? '') {
            $fields['name'] = $name;
        }
        foreach (self::META_FIELDS as $key) {
            if (array_key_exists($key, $arguments)) {
                $fields[$key] = (string) $arguments[$key];
            }
        }

        $translations = [];
        if ($fields) {
            $translations[locale_code()] = $fields;
        }
        if ($extra = $arguments['translations'] ?? []) {
            // New locale rows require a name (NOT NULL column); existing locales accept partial updates.
            $existingLocales = $category->translations()->pluck('locale')->all();
            $allowed         = ['name', ...self::META_FIELDS];
            foreach ((array) $extra as $loc => $locFields) {
                if (! is_array($locFields)) {
                    continue;
                }
                $entry = array_intersect_key($locFields, array_flip($allowed));
                if ($entry && (in_array($loc, $existingLocales) || ! empty($entry['name']))) {
                    $translations[$loc] = array_merge($translations[$loc] ?? [], $entry);
                }
            }
        }
        if ($translations) {
            $data['translations'] = $translations;
        }

        if (empty($data)) {
            throw new InvalidArgumentException('No fields to update.');
        }

        CategoryRepo::getInstance()->patch($category, $data);

        $category->refresh()->load('translation', 'translations');

        return [
            'id'               => $category->id,
            'name'             => $category->fallbackName(),
            'slug'             => $category->slug,
            'active'           => (bool) $category->active,
            'meta_title'       => $category->translation->meta_title ?? '',
            'meta_description' => $category->translation->meta_description ?? '',
            'meta_keywords'    => $category->translation->meta_keywords ?? '',
            'translations'     => $category->translations->map(fn ($t) => [
                'locale'           => $t->locale,
                'name'             => $t->name ?? '',
                'meta_title'       => $t->meta_title ?? '',
                'meta_description' => $t->meta_description ?? '',
                'meta_keywords'    => $t->meta_keywords ?? '',
            ])->values()->all(),
        ];
    }
}
