<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Front\Services\SitemapService;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Sitemap\Sitemap;
use Tests\TestCase;

/**
 * The sitemap is built per locale, so the routes it links to have to be
 * resolved through the locale prefixed route names. The old implementation
 * hardcoded the locale-less route name and threw on a multi-language store.
 */
class SitemapServiceTest extends TestCase
{
    #[Test]
    public function a_missing_route_resolves_to_an_empty_string(): void
    {
        // No route named "definitely.not.a.route" exists, so the resolver
        // must degrade to an empty string instead of throwing.
        $this->assertSame('', SitemapService::frontRoute('en', 'definitely.not.a.route'));
    }

    #[Test]
    public function the_misc_sitemap_only_lists_the_indexable_pages(): void
    {
        $sitemap = Sitemap::create();

        $service = new SitemapService;
        $method  = new \ReflectionMethod($service, 'addMisc');
        $method->invoke($service, $sitemap, 'en');

        $urls = [];
        foreach ($sitemap->getTags() as $tag) {
            $urls[] = $tag->url;
        }

        $this->assertNotEmpty($urls, 'the misc sitemap should not be empty');

        foreach ($urls as $url) {
            $this->assertStringNotContainsString('/login', $url);
            $this->assertStringNotContainsString('/register', $url);
        }
    }
}
