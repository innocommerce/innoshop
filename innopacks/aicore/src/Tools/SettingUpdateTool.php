<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Common\Repositories\SettingRepo;
use InvalidArgumentException;

class SettingUpdateTool extends BaseTool
{
    protected bool $write = true;

    public function name(): string
    {
        return 'setting_update';
    }

    public function description(): string
    {
        return 'Update system-level SEO settings: meta title, description and keywords, per locale (PATCH semantics). These are the site-wide defaults used by pages without their own SEO meta, such as the homepage.';
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => [
                'meta_title'       => ['type' => 'string', 'description' => 'Meta title (default locale)'],
                'meta_description' => ['type' => 'string', 'description' => 'Meta description (default locale)'],
                'meta_keywords'    => ['type' => 'string', 'description' => 'Meta keywords, comma separated (default locale)'],
                'translations'     => self::translationsSchema([
                    'meta_title'       => 'Meta title in this locale',
                    'meta_description' => 'Meta description in this locale',
                    'meta_keywords'    => 'Meta keywords, comma separated, in this locale',
                ], 'Per-locale overrides keyed by locale code (multilingual), e.g. {"zh-cn": {"meta_title": "SEO 标题"}}. Same-locale entries override top-level fields.'),
            ],
        ];
    }

    public function requiredPermission(): ?string
    {
        return 'settings_update';
    }

    public function execute(array $arguments): mixed
    {
        $values    = SettingDetailTool::currentValues();
        $byLocale  = [];
        $hasChange = false;

        $locale = locale_code();
        foreach (self::META_FIELDS as $field) {
            if (array_key_exists($field, $arguments)) {
                $byLocale[$field][$locale] = (string) $arguments[$field];
                $hasChange                 = true;
            }
        }

        foreach ((array) ($arguments['translations'] ?? []) as $loc => $fields) {
            if (! is_array($fields) || ! in_array($loc, enabled_locale_codes(), true)) {
                continue;
            }
            foreach (self::META_FIELDS as $field) {
                if (array_key_exists($field, $fields)) {
                    $byLocale[$field][$loc] = (string) $fields[$field];
                    $hasChange              = true;
                }
            }
        }

        if (! $hasChange) {
            throw new InvalidArgumentException('No fields to update.');
        }

        $payload = [];
        foreach (self::META_FIELDS as $field) {
            $merged = array_merge($values[$field], $byLocale[$field] ?? []);
            if ($merged !== []) {
                $payload[$field] = $merged;
            }
        }

        SettingRepo::getInstance()->updateValues($payload, 'system');

        // updateValues() nulls the cached config; rebuild it straight from the
        // DB so the response reflects what was just persisted.
        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);

        return SettingDetailTool::currentValues();
    }
}
