<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Throwable;

/**
 * Resolves a storefront locale code from a visitor's IP address.
 *
 * The IP → country step is already provided by {@see GeoLocationService}, this
 * class only owns the country → locale mapping and the fallback rules:
 *
 *   1. exact country match           → the mapped locale
 *   2. same-language regional variant → e.g. "pt-pt" falls back to "pt-br"
 *   3. no match                       → null, the caller falls back to the
 *                                       storefront default locale
 */
class CountryLocaleResolver
{
    /**
     * ISO 3166-1 alpha-2 country code → storefront locale code.
     *
     * Only countries whose primary language is available in the storefront are
     * listed. A country that is absent here is treated as "no language for this
     * country" and falls back to the storefront default locale.
     *
     * TW (中国台湾) is included alongside the country codes: it is a region
     * rather than a country, and its visitors are served the traditional
     * Chinese locale.
     *
     * Plugins may extend or override this map through the
     * `common.locale.country_map` hook.
     *
     * @var array<string, string>
     */
    public const COUNTRY_TO_LOCALE = [
        // 阿拉伯语
        'AE' => 'ar', 'BH' => 'ar', 'DJ' => 'ar', 'DZ' => 'ar', 'EG' => 'ar',
        'IQ' => 'ar', 'JO' => 'ar', 'KW' => 'ar', 'LB' => 'ar', 'LY' => 'ar',
        'MA' => 'ar', 'MR' => 'ar', 'OM' => 'ar', 'PS' => 'ar', 'QA' => 'ar',
        'SA' => 'ar', 'SD' => 'ar', 'SY' => 'ar', 'TN' => 'ar', 'YE' => 'ar',

        // 德语
        'AT' => 'de', 'CH' => 'de', 'DE' => 'de', 'LI' => 'de',

        // 英语
        'AU' => 'en', 'BW' => 'en', 'CA' => 'en', 'GH' => 'en', 'IE' => 'en',
        'IN' => 'en', 'KE' => 'en', 'NG' => 'en', 'NZ' => 'en', 'PH' => 'en',
        'SG' => 'en', 'TZ' => 'en', 'UG' => 'en', 'US' => 'en', 'ZA' => 'en',
        'ZW' => 'en',

        // 西班牙语
        'AR' => 'es', 'BO' => 'es', 'CL' => 'es', 'CO' => 'es', 'CR' => 'es',
        'CU' => 'es', 'DO' => 'es', 'EC' => 'es', 'ES' => 'es', 'GT' => 'es',
        'HN' => 'es', 'MX' => 'es', 'NI' => 'es', 'PA' => 'es', 'PE' => 'es',
        'PR' => 'es', 'PY' => 'es', 'SV' => 'es', 'UY' => 'es', 'VE' => 'es',

        // 法语
        'BF' => 'fr', 'BJ' => 'fr', 'CD' => 'fr', 'CF' => 'fr', 'CG' => 'fr',
        'CI' => 'fr', 'CM' => 'fr', 'FR' => 'fr', 'GA' => 'fr', 'GN' => 'fr',
        'HT' => 'fr', 'LU' => 'fr', 'MC' => 'fr', 'ML' => 'fr', 'NE' => 'fr',
        'RE' => 'fr', 'SN' => 'fr', 'TD' => 'fr', 'TG' => 'fr',

        // 印尼语
        'ID' => 'id',

        // 意大利语
        'IT' => 'it', 'SM' => 'it', 'VA' => 'it',

        // 日语
        'JP' => 'ja',

        // 高棉语
        'KH' => 'km',

        // 韩语
        'KR' => 'ko',

        // 马来语
        'MY' => 'ms',

        // 荷兰语
        'BE' => 'nl', 'NL' => 'nl',

        // 葡萄牙语（巴西 / 葡萄牙）
        'BR' => 'pt-br', 'PT' => 'pt-pt',

        // 俄语
        'RU' => 'ru', 'TJ' => 'ru', 'TM' => 'ru', 'UZ' => 'ru',

        // 泰语
        'TH' => 'th',

        // 土耳其语
        'TR' => 'tr',

        // 越南语
        'VN' => 'vi',

        // 简体中文
        'CN' => 'zh-cn',

        // 繁体中文（香港 / 澳门 / 台湾地区）
        'HK' => 'zh-hk', 'MO' => 'zh-hk', 'TW' => 'zh-hk',
    ];

    /**
     * Same-language regional variants, used when the mapped locale is not
     * available in the storefront but another variant of the same language is.
     *
     * @var array<string, string[]>
     */
    public const LANGUAGE_VARIANTS = [
        'zh-cn' => ['zh-hk'],
        'zh-hk' => ['zh-cn'],
        'pt-br' => ['pt-pt'],
        'pt-pt' => ['pt-br'],
    ];

    /**
     * Get the country → locale map, allowing plugins to extend or override it.
     *
     * @return array<string, string>
     */
    public function countryMap(): array
    {
        $map = static::COUNTRY_TO_LOCALE;

        if (! function_exists('fire_hook_filter')) {
            return $map;
        }

        try {
            $hooked = fire_hook_filter('common.locale.country_map', $map);
        } catch (Throwable $e) {
            return $map;
        }

        return is_array($hooked) ? $hooked : $map;
    }

    /**
     * Map a country code to a storefront locale code without checking whether
     * that locale is actually enabled.
     *
     * @param  string|null  $countryCode
     * @param  array<string, string>  $map
     */
    public static function mapCountry(?string $countryCode, array $map = []): ?string
    {
        if ($countryCode === null || $countryCode === '') {
            return null;
        }

        $map = $map ?: static::COUNTRY_TO_LOCALE;

        return $map[strtoupper(trim($countryCode))] ?? null;
    }

    /**
     * Resolve the storefront locale for a country code.
     *
     * @param  string|null  $countryCode
     * @param  string[]  $availableLocales
     * @param  array<string, string>  $map
     */
    public static function resolve(?string $countryCode, array $availableLocales, array $map = []): ?string
    {
        $locale = static::mapCountry($countryCode, $map);
        if ($locale === null) {
            return null;
        }

        if (in_array($locale, $availableLocales, true)) {
            return $locale;
        }

        foreach (static::LANGUAGE_VARIANTS[$locale] ?? [] as $variant) {
            if (in_array($variant, $availableLocales, true)) {
                return $variant;
            }
        }

        return null;
    }

    /**
     * Resolve the storefront locale for a visitor IP address.
     *
     * @param  string  $ip
     * @param  string[]  $availableLocales
     */
    public function resolveFromIp(string $ip, array $availableLocales): ?string
    {
        $countryCode = $this->countryCodeFromIp($ip);
        if ($countryCode === null) {
            return null;
        }

        return static::resolve($countryCode, $availableLocales, $this->countryMap());
    }

    /**
     * Look up the ISO 3166-1 alpha-2 country code for an IP address.
     *
     * @param  string  $ip
     */
    public function countryCodeFromIp(string $ip): ?string
    {
        if ($ip === '' || $ip === '127.0.0.1' || $ip === '::1') {
            return null;
        }

        $location    = geo_location($ip);
        $countryCode = strtoupper(trim((string) ($location['country_code'] ?? '')));

        return $countryCode === '' ? null : $countryCode;
    }
}
