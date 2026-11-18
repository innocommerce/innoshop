<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\CountryLocaleResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CountryLocaleResolverTest extends TestCase
{
    #[Test]
    public function it_resolves_a_locale_for_a_mapped_country(): void
    {
        $this->assertSame('zh-cn', CountryLocaleResolver::resolve('CN', ['en', 'zh-cn']));
        $this->assertSame('ja', CountryLocaleResolver::resolve('JP', ['en', 'ja']));
        $this->assertSame('de', CountryLocaleResolver::resolve('DE', ['en', 'de']));
        $this->assertSame('ko', CountryLocaleResolver::resolve('KR', ['en', 'ko']));
        $this->assertSame('pt-br', CountryLocaleResolver::resolve('BR', ['en', 'pt-br']));
        $this->assertSame('pt-pt', CountryLocaleResolver::resolve('PT', ['en', 'pt-pt']));
    }

    #[Test]
    public function it_matches_country_codes_case_insensitively_and_trims_whitespace(): void
    {
        $this->assertSame('zh-cn', CountryLocaleResolver::resolve('cn', ['zh-cn']));
        $this->assertSame('zh-cn', CountryLocaleResolver::resolve(' CN ', ['zh-cn']));
    }

    #[Test]
    public function it_returns_null_for_a_country_without_a_mapped_language(): void
    {
        // 波兰语不在站内语言列表中 —— 按需求应回退后台默认语言，此处返回 null 由调用方回退
        $this->assertNull(CountryLocaleResolver::resolve('PL', ['en', 'zh-cn']));
        $this->assertNull(CountryLocaleResolver::resolve('XX', ['en', 'zh-cn']));
        $this->assertNull(CountryLocaleResolver::resolve(null, ['en', 'zh-cn']));
        $this->assertNull(CountryLocaleResolver::resolve('', ['en', 'zh-cn']));
    }

    #[Test]
    public function it_returns_null_when_the_mapped_locale_is_not_available(): void
    {
        $this->assertNull(CountryLocaleResolver::resolve('CN', ['en', 'ja']));
        $this->assertNull(CountryLocaleResolver::resolve('CN', []));
    }

    #[Test]
    public function it_falls_back_to_a_same_language_regional_variant(): void
    {
        // 站内只有 pt-br 时，葡萄牙访客应命中 pt-br 而不是回退默认语言
        $this->assertSame('pt-br', CountryLocaleResolver::resolve('PT', ['en', 'pt-br']));
        // 站内只有 zh-hk 时，中国大陆访客应命中 zh-hk
        $this->assertSame('zh-hk', CountryLocaleResolver::resolve('CN', ['en', 'zh-hk']));
        // 站内只有 zh-cn 时，香港访客应命中 zh-cn
        $this->assertSame('zh-cn', CountryLocaleResolver::resolve('HK', ['en', 'zh-cn']));
    }

    #[Test]
    public function it_returns_null_when_no_same_language_variant_is_available(): void
    {
        $this->assertNull(CountryLocaleResolver::resolve('PT', ['en', 'zh-cn']));
    }

    #[Test]
    public function it_accepts_a_custom_country_map(): void
    {
        $map = ['CN' => 'ja', 'JP' => 'zh-cn'];

        $this->assertSame('ja', CountryLocaleResolver::resolve('CN', ['en', 'ja'], $map));
        $this->assertSame('zh-cn', CountryLocaleResolver::resolve('JP', ['en', 'zh-cn'], $map));
        $this->assertNull(CountryLocaleResolver::resolve('DE', ['en', 'de'], $map));
    }

    #[Test]
    public function map_country_ignores_locale_availability(): void
    {
        $this->assertSame('zh-cn', CountryLocaleResolver::mapCountry('CN'));
        $this->assertNull(CountryLocaleResolver::mapCountry('CN', ['XX' => 'ja']));
        $this->assertNull(CountryLocaleResolver::mapCountry('PL'));
        $this->assertNull(CountryLocaleResolver::mapCountry(null));
    }

    #[Test]
    public function the_built_in_map_covers_the_major_storefront_languages(): void
    {
        $map = CountryLocaleResolver::COUNTRY_TO_LOCALE;

        $this->assertArrayHasKey('CN', $map);
        $this->assertArrayHasKey('JP', $map);
        $this->assertArrayHasKey('HK', $map);
        $this->assertArrayHasKey('MO', $map);
        $this->assertArrayHasKey('KR', $map);
        // 台湾是地区而非国家，访客按繁体中文处理
        $this->assertArrayHasKey('TW', $map);
        $this->assertSame('zh-hk', $map['TW']);
    }

    #[Test]
    public function every_mapped_country_points_at_a_well_formed_locale_code(): void
    {
        $map = CountryLocaleResolver::COUNTRY_TO_LOCALE;

        $this->assertNotEmpty($map);

        foreach ($map as $countryCode => $locale) {
            $this->assertMatchesRegularExpression('/^[A-Z]{2}$/', $countryCode);
            $this->assertMatchesRegularExpression('/^[a-z]{2}(-[a-z]{2})?$/', $locale);

            foreach (CountryLocaleResolver::LANGUAGE_VARIANTS[$locale] ?? [] as $variant) {
                $this->assertNotSame(
                    $locale,
                    $variant,
                    "语言 {$locale} 的变体组不应包含自身"
                );
                $this->assertMatchesRegularExpression('/^[a-z]{2}(-[a-z]{2})?$/', $variant);
            }
        }
    }
}
