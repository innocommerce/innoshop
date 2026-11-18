<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Services;

use InnoShop\Common\Services\CountryLocaleResolver;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Integration tests for the IP → country → locale chain against the real
 * GeoLite2 database.
 *
 * The tests are skipped when the database has not been installed, which is the
 * expected state of a fresh installation.
 */
class CountryLocaleResolverGeoTest extends TestCase
{
    /**
     * IP addresses with a stable country assignment.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function ipProvider(): array
    {
        return [
            'china'   => ['114.114.114.114', 'zh-cn'],
            'japan'   => ['133.242.0.1', 'ja'],
            'usa'     => ['8.8.8.8', 'en'],
            'brazil'  => ['200.160.2.3', 'pt-br'],
            'germany' => ['95.90.28.0', 'de'],
            'france'  => ['212.227.81.4', 'fr'],
        ];
    }

    #[Test]
    #[DataProvider('ipProvider')]
    public function it_resolves_the_locale_of_a_visitor_ip(string $ip, string $expectedLocale): void
    {
        $this->skipWithoutGeoDatabase();

        $resolver = new CountryLocaleResolver;

        $this->assertSame(
            $expectedLocale,
            $resolver->resolveFromIp($ip, ['en', 'de', 'fr', 'ja', 'pt-br', 'zh-cn'])
        );
    }

    #[Test]
    public function it_returns_null_for_an_address_without_a_country(): void
    {
        $this->skipWithoutGeoDatabase();

        $resolver = new CountryLocaleResolver;

        $this->assertNull($resolver->resolveFromIp('127.0.0.1', ['en', 'zh-cn']));
        $this->assertNull($resolver->resolveFromIp('::1', ['en', 'zh-cn']));
    }

    #[Test]
    public function it_returns_null_for_a_country_without_an_available_locale(): void
    {
        $this->skipWithoutGeoDatabase();

        $resolver = new CountryLocaleResolver;

        // 德国访客，但站内只启用了英文和中文
        $this->assertNull($resolver->resolveFromIp('95.90.28.0', ['en', 'zh-cn']));
    }

    #[Test]
    public function it_falls_back_to_a_same_language_variant_when_the_exact_locale_is_missing(): void
    {
        $this->skipWithoutGeoDatabase();

        $resolver = new CountryLocaleResolver;

        // 巴西访客，站内只有 pt-pt 时应命中同语言的 pt-br
        $this->assertSame('pt-br', $resolver->resolveFromIp('200.160.2.3', ['en', 'pt-br']));
    }

    /**
     * Skip the test when the GeoLite2 database has not been installed.
     */
    private function skipWithoutGeoDatabase(): void
    {
        $path = (string) config('innoshop.geo_lite_path');

        if ($path === '' || ! file_exists($path)) {
            $this->markTestSkipped('The GeoLite2 database is not installed');
        }
    }
}
