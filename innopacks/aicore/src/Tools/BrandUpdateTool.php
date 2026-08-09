<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Models\Brand;
use InnoShop\Common\Repositories\BrandRepo;
use InvalidArgumentException;

class BrandUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'brand_update';
    }

    public function description(): string
    {
        return 'Update an existing product brand. Only provided fields are changed (PATCH semantics).';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'id'               => ['type' => 'integer', 'description' => 'Brand ID'],
                'name'             => ['type' => 'string', 'description' => 'New brand name (default locale)'],
                'slug'             => ['type' => 'string', 'description' => 'New URL slug'],
                'first'            => ['type' => 'string', 'description' => 'New first letter'],
                'logo'             => ['type' => 'string', 'description' => 'New logo image URL'],
                'position'         => ['type' => 'integer', 'description' => 'Display position'],
                'active'           => ['type' => 'boolean', 'description' => 'Active status'],
                'meta_title'       => ['type' => 'string', 'description' => 'SEO meta title (default locale)'],
                'meta_description' => ['type' => 'string', 'description' => 'SEO meta description (default locale)'],
                'meta_keywords'    => ['type' => 'string', 'description' => 'SEO meta keywords, comma separated (default locale)'],
                'translations'     => self::translationsSchema([
                    'name'             => 'Brand name in this locale. Required when adding a new locale.',
                    'meta_title'       => 'SEO meta title in this locale',
                    'meta_description' => 'SEO meta description in this locale',
                    'meta_keywords'    => 'SEO meta keywords, comma separated, in this locale',
                ], 'Per-locale overrides keyed by locale code (multilingual), e.g. {"zh-cn": {"name": "品牌名", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Adding a new locale requires name. Same-locale entries override top-level fields.'),
            ],
            'required' => ['id'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'brands_update';
    }

    public function execute(array $arguments): mixed
    {
        $brand = Brand::query()->find((int) ($arguments['id'] ?? 0));
        if (! $brand) {
            throw new InvalidArgumentException("Brand [{$arguments['id']}] not found.");
        }

        // BrandRepo::update() replaces all scalar fields and translations, so
        // carry over current values and only override what was provided.
        $hasScalar = false;
        $data      = ['name' => $brand->name];
        foreach (['slug', 'first', 'logo', 'position', 'active'] as $key) {
            if (array_key_exists($key, $arguments)) {
                $data[$key] = $arguments[$key];
                $hasScalar  = true;
            } else {
                $data[$key] = $brand->{$key};
            }
        }

        $hasName = (string) ($arguments['name'] ?? '') !== '';
        if ($hasName) {
            $data['name'] = (string) $arguments['name'];
        }

        // Merge default-locale overrides (name + SEO TDK) onto existing translations.
        $brand->loadMissing('translations');
        $byLocale = [];
        foreach ($brand->translations as $item) {
            $byLocale[$item->locale] = $item->only($item->getFillable());
        }

        $fields = [];
        if ($hasName) {
            $fields['name'] = $data['name'];
        }
        foreach (self::META_FIELDS as $key) {
            if (array_key_exists($key, $arguments)) {
                $fields[$key] = (string) $arguments[$key];
            }
        }

        $hasTranslationChanges = (bool) $fields;
        if ($fields) {
            $locale            = locale_code();
            $byLocale[$locale] = array_merge($byLocale[$locale] ?? ['locale' => $locale], $fields);
        }

        if ($extra = $arguments['translations'] ?? []) {
            $allowed = ['name', ...self::META_FIELDS];
            foreach ((array) $extra as $loc => $locFields) {
                if (! is_array($locFields)) {
                    continue;
                }
                // New locale rows require a name (NOT NULL column); existing locales accept partial updates.
                $entry = array_intersect_key($locFields, array_flip($allowed));
                if ($entry && (isset($byLocale[$loc]) || ! empty($entry['name']))) {
                    $byLocale[$loc]        = array_merge($byLocale[$loc] ?? ['locale' => $loc], $entry);
                    $hasTranslationChanges = true;
                }
            }
        }

        if (! $hasScalar && ! $hasName && ! $hasTranslationChanges) {
            throw new InvalidArgumentException('No fields to update.');
        }

        if ($hasTranslationChanges) {
            $data['translations'] = array_values($byLocale);
        }

        BrandRepo::getInstance()->update($brand, $data);

        $brand->refresh()->load('translation', 'translations');

        return [
            'id'               => $brand->id,
            'name'             => $brand->name,
            'slug'             => $brand->slug,
            'active'           => (bool) $brand->active,
            'meta_title'       => $brand->translation->meta_title ?? '',
            'meta_description' => $brand->translation->meta_description ?? '',
            'meta_keywords'    => $brand->translation->meta_keywords ?? '',
            'translations'     => $brand->translations->map(fn ($t) => [
                'locale'           => $t->locale,
                'name'             => $t->name ?? '',
                'meta_title'       => $t->meta_title ?? '',
                'meta_description' => $t->meta_description ?? '',
                'meta_keywords'    => $t->meta_keywords ?? '',
            ])->values()->all(),
        ];
    }
}
