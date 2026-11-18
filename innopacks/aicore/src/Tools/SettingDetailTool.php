<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

class SettingDetailTool extends BaseTool
{
    public function name(): string
    {
        return 'setting_detail';
    }

    public function description(): string
    {
        return 'Get the system-level SEO settings (meta title, description, keywords) for every enabled locale. These are the site-wide defaults used by pages without their own SEO meta, such as the homepage.';
    }

    public function requiredPermission(): ?string
    {
        return 'settings_index';
    }

    public function execute(array $arguments): mixed
    {
        return self::currentValues();
    }

    /**
     * Field => locale => text. Includes locales found in stored JSON even when
     * disabled, so existing values never disappear from the response.
     */
    public static function currentValues(): array
    {
        $result = [];
        foreach (self::META_FIELDS as $field) {
            $raw = system_setting($field);
            if (is_array($raw)) {
                $byLocale = array_map(fn ($value) => (string) $value, $raw);
            } elseif (is_string($raw) && $raw !== '') {
                $byLocale = [locale_code() => $raw];
            } else {
                $byLocale = [];
            }

            $codes = array_unique(array_merge(enabled_locale_codes(), array_keys($byLocale)));

            $values = [];
            foreach ($codes as $code) {
                $values[$code] = $byLocale[$code] ?? '';
            }
            $result[$field] = $values;
        }

        return $result;
    }
}
