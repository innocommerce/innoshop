<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\BotClassifier;
use PHPUnit\Framework\TestCase;

class BotClassifierTest extends TestCase
{
    public function test_classifies_known_brands(): void
    {
        $cases = [
            'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)' => 'google',
            'Mozilla/5.0 (compatible; bingbot/2.0)'                                    => 'bing',
            'GPTBot/1.1'                                                               => 'openai',
            'claudebot'                                                                => 'anthropic',
            'Mozilla/5.0 (compatible; PerplexityBot/1.0)'                              => 'perplexity',
            'curl/8.4.0'                                                               => 'curl',
            'Python/3.12 aiohttp/3.9'                                                  => 'python',
            'Mozilla/5.0 (compatible; AhrefsBot/7.0)'                                  => 'ahrefs',
            'Mozilla/5.0 (compatible; Baiduspider/2.0)'                                => 'baidu',
        ];

        foreach ($cases as $ua => $brand) {
            $result = BotClassifier::classify($ua);
            $this->assertTrue($result['is_bot'], "UA should be bot: {$ua}");
            $this->assertSame($brand, $result['brand'], "UA should map to {$brand}: {$ua}");
            $this->assertSame(BotClassifier::categoryOf($brand), $result['category']);
        }
    }

    public function test_brand_match_wins_over_generic_keywords(): void
    {
        // "googlebot" contains the generic "bot" keyword, but the specific
        // brand must win.
        $result = BotClassifier::classify('Mozilla/5.0 (compatible; Googlebot/2.1)');
        $this->assertSame('google', $result['brand']);
        $this->assertSame('seo', $result['category']);
    }

    public function test_generic_bot_keyword_falls_back_to_unknown(): void
    {
        $result = BotClassifier::classify('SomeUnknownCorp-Crawler/1.0');
        $this->assertTrue($result['is_bot']);
        $this->assertSame('unknown', $result['brand']);
        $this->assertSame('suspicious', $result['category']);
    }

    public function test_empty_user_agent_is_bot(): void
    {
        $result = BotClassifier::classify('');
        $this->assertTrue($result['is_bot']);
        $this->assertSame('unknown', $result['brand']);
    }

    public function test_real_browser_is_not_bot(): void
    {
        $result = BotClassifier::classify('Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Safari/605.1.15');
        $this->assertFalse($result['is_bot']);
        $this->assertSame('', $result['brand']);
    }

    public function test_google_fetchers_without_bot_token_are_matched(): void
    {
        // GoogleOther / inspection tool UAs deliberately omit "bot" — they
        // slipped every keyword list in the field (24k sessions).
        $cases = [
            'Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/153.0.8010.52 Mobile Safari/537.36 (compatible; GoogleOther)' => 'googleother',
            'GoogleOther'                                                                                                                                                          => 'googleother',
            'Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; Google-InspectionTool/1.0)'                                                                            => 'google_inspection',
            'Storebot-Google/1.0'                                                                                                                                                  => 'google_inspection',
        ];

        foreach ($cases as $ua => $brand) {
            $result = BotClassifier::classify($ua);
            $this->assertTrue($result['is_bot'], "UA should be bot: {$ua}");
            $this->assertSame($brand, $result['brand']);
            $this->assertSame('other', $result['category'], "Google fetcher should be other bucket: {$ua}");
        }
    }

    public function test_geo_bucket_covers_ai_crawlers(): void
    {
        foreach (['GPTBot/1.1', 'ClaudeBot', 'PerplexityBot/1.0', 'CCBot/2.0'] as $ua) {
            $result = BotClassifier::classify($ua);
            $this->assertSame('geo', $result['category'], "AI crawler should be geo bucket: {$ua}");
        }
    }
}
