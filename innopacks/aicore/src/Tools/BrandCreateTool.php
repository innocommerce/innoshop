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
use InnoShop\Common\Repositories\BrandRepo;
use InvalidArgumentException;

class BrandCreateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'brand_create';
    }

    public function description(): string
    {
        return 'Create a new product brand with translations. Slug is auto-generated from the name if omitted.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'name'             => ['type' => 'string', 'description' => 'Brand name (default locale)'],
                'slug'             => ['type' => 'string', 'description' => 'URL slug, auto-generated if empty'],
                'first'            => ['type' => 'string', 'description' => 'First letter for brand index, auto-detected if empty'],
                'logo'             => ['type' => 'string', 'description' => 'Logo image URL'],
                'active'           => ['type' => 'boolean', 'description' => 'Active status, default true'],
                'meta_title'       => ['type' => 'string', 'description' => 'SEO meta title (default locale)'],
                'meta_description' => ['type' => 'string', 'description' => 'SEO meta description (default locale)'],
                'meta_keywords'    => ['type' => 'string', 'description' => 'SEO meta keywords, comma separated (default locale)'],
                'translations'     => self::translationsSchema([
                    'name'             => 'Brand name in this locale. Required when adding a new locale.',
                    'meta_title'       => 'SEO meta title in this locale',
                    'meta_description' => 'SEO meta description in this locale',
                    'meta_keywords'    => 'SEO meta keywords, comma separated, in this locale',
                ], 'Additional locale translations (multilingual), e.g. {"zh-cn": {"name": "品牌名", "meta_title": "SEO 标题"}}. Each locale key maps to an entry with the fields below. Entries for the default locale merge over top-level fields (no duplicate row).'),
            ],
            'required' => ['name'],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'brands_create';
    }

    public function execute(array $arguments): mixed
    {
        $name   = (string) ($arguments['name'] ?? '');
        $active = array_key_exists('active', $arguments) ? (bool) $arguments['active'] : true;

        if ($name === '') {
            throw new InvalidArgumentException('Brand name is required.');
        }

        $data = [
            'slug'         => $arguments['slug'] ?? Str::slug($name),
            'first'        => $arguments['first'] ?? mb_substr($name, 0, 1),
            'active'       => $active,
            'position'     => 0,
            'translations' => [],
        ];

        if ($logo = $arguments['logo'] ?? '') {
            $data['logo'] = $logo;
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

        $brand = BrandRepo::getInstance()->create($data);
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
