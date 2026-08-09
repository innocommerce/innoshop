<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Support\Str;
use InnoShop\Common\Repositories\CategoryRepo;
use InvalidArgumentException;

class CategoryCreateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'category_create';
    }

    public function description(): string
    {
        return 'Create a new product category with translations. Slug is auto-generated from the name if omitted.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'name'             => ['type' => 'string', 'description' => 'Category name (default locale)'],
                'slug'             => ['type' => 'string', 'description' => 'URL slug, auto-generated if empty'],
                'parent_id'        => ['type' => 'integer', 'description' => 'Parent category ID, 0 for root'],
                'active'           => ['type' => 'boolean', 'description' => 'Active status, default true'],
                'meta_title'       => ['type' => 'string', 'description' => 'SEO meta title (default locale)'],
                'meta_description' => ['type' => 'string', 'description' => 'SEO meta description (default locale)'],
                'meta_keywords'    => ['type' => 'string', 'description' => 'SEO meta keywords, comma separated (default locale)'],
                'translations'     => self::translationsSchema([
                    'name'             => 'Category name in this locale. Required when adding a new locale.',
                    'meta_title'       => 'SEO meta title in this locale',
                    'meta_description' => 'SEO meta description in this locale',
                    'meta_keywords'    => 'SEO meta keywords, comma separated, in this locale',
                ], 'Additional locale translations (multilingual), e.g. {"zh-cn": {"name": "分类名", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Entries for the default locale merge over top-level fields (no duplicate row).'),
            ],
            'required' => ['name'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'categories_create';
    }

    public function execute(array $arguments): mixed
    {
        $name   = (string) ($arguments['name'] ?? '');
        $slug   = (string) ($arguments['slug'] ?? '');
        $active = array_key_exists('active', $arguments) ? (bool) $arguments['active'] : true;

        if ($name === '') {
            throw new InvalidArgumentException('Category name is required.');
        }

        $data = [
            'slug'         => $slug ?: Str::slug($name),
            'position'     => 0,
            'active'       => $active,
            'translations' => [],
        ];

        if ($parentId = $arguments['parent_id'] ?? 0) {
            $data['parent_id'] = (int) $parentId;
        }

        // Build translations keyed by locale: an entry for the default locale in
        // `translations` merges over (overrides) top-level fields, no duplicate row.
        $locale      = locale_code();
        $translation = ['locale' => $locale, 'name' => $name];
        foreach (self::META_FIELDS as $key) {
            if ($arguments[$key] ?? '') {
                $translation[$key] = (string) $arguments[$key];
            }
        }
        $byLocale = [$locale => $translation];

        if ($extra = $arguments['translations'] ?? []) {
            $allowed = ['name', ...self::META_FIELDS];
            foreach ((array) $extra as $loc => $fields) {
                if (! is_array($fields)) {
                    continue;
                }
                $entry = array_intersect_key($fields, array_flip($allowed));
                // New locale rows require a name; the default-locale entry already has one.
                if ($entry && ($loc === $locale || ! empty($entry['name']))) {
                    $byLocale[$loc] = array_merge($byLocale[$loc] ?? ['locale' => $loc], $entry);
                }
            }
        }
        $data['translations'] = array_values($byLocale);

        $category = CategoryRepo::getInstance()->create($data);
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
