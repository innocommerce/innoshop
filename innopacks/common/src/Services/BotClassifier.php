<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Facades\Lang;

/**
 * Single source of truth for bot detection and brand/category classification.
 *
 * Three consumers share this so the taxonomy can never drift:
 *   - VisitTrackingService  — live request routing (bot hits go to bot_daily, never visits)
 *   - VisitRepo             — panel bot analytics reads
 *   - visits:migrate-bots   — historical backfill of legacy visits rows
 *
 * Categories are the four panel buckets:
 *   seo — search crawlers (being indexed drives SEO)
 *   geo — AI / LLM crawlers (being cited drives GEO)
 *   other — friendly bots: SEO tools, monitors, social, archive
 *   suspicious — scanners, generic libs, unknowns (usually hostile)
 */
class BotClassifier
{
    /**
     * Generic bot UA keywords. A UA matching any of these (but no specific
     * brand below) is still a bot — brand falls back to "unknown".
     *
     * @var string[]
     */
    protected const GENERIC_BOT_PATTERNS = [
        'bot', 'crawler', 'spider', 'scrap', 'scout', 'scan', 'check', 'fetch',
        'archive', 'heritrix', 'slurp', 'teoma', 'ia_archiver',
        'monitor', 'uptime', 'pingdom', 'statuscake', 'site24x7', 'newrelicpinger',
        'masscan', 'nmap', 'nikto', 'sqlmap', 'wpscan', 'dirbuster', 'gobuster',
        'zgrab', 'censys', 'shodan', 'shadow',
    ];

    /**
     * Known bot brand patterns keyed by brand code. Brand match wins over the
     * generic keywords, so keep specific patterns first in mind: matching is
     * "longest / most specific brand list first" via two passes (brand pass,
     * then generic pass).
     *
     * Keep this list aligned with Console\Commands\TagBots::$botPatterns so
     * newly tagged visits remain classifiable in the analytics view.
     *
     * @return array<string, string[]>
     */
    public static function brandPatterns(): array
    {
        return [
            'google' => ['googlebot'],
            // Google fetchers whose UA deliberately carries no "bot" token
            // (Google docs: verify via rDNS). Confirmed in the field from the
            // 66.249.x.x range: 24k sessions slipped through every keyword list.
            'googleother'       => ['googleother'],
            'google_inspection' => ['google-inspectiontool', 'storebot-google'],
            'bing'              => ['bingbot'],
            'baidu'             => ['baiduspider'],
            'sogou'             => ['sogou'],
            'shenma'            => ['yisouspider'],
            'bytespider'        => ['bytespider'],
            'petalbot'          => ['petalbot'],
            'duckduckgo'        => ['duckduckbot'],
            'yandex'            => ['yandexbot', 'yadirectfetcher'],
            'spider360'         => ['360spider'],
            'coccoc'            => ['coccocbot'],
            // AI / LLM training & search
            'openai'     => ['gptbot', 'chatgpt-user', 'oai-searchbot'],
            'anthropic'  => ['claudebot', 'claude-user'],
            'perplexity' => ['perplexitybot'],
            'ccbot'      => ['ccbot'],
            'meta'       => ['meta-externalagent', 'meta-webindexer', 'facebot', 'facebookexternalhit'],
            'you'        => ['youbot'],
            'amazon'     => ['amazonbot'],
            'applebot'   => ['applebot'],
            'ai_index'   => ['aiwebindex', 'haibara_ai'],
            // SEO / marketing analytics
            'ahrefs'      => ['ahrefsbot'],
            'semrush'     => ['semrushbot'],
            'mj12'        => ['mj12bot'],
            'dotbot'      => ['dotbot'],
            'exabot'      => ['exabot'],
            'serpstat'    => ['serpstatbot'],
            'seranking'   => ['serankingbacklinksbot'],
            'dataforseo'  => ['dataforseobot'],
            'brokenlinks' => ['brokenlinksbot'],
            'velen'       => ['velenpublicwebcrawler'],
            'webtrackr'   => ['webtrackrcrawler'],
            'surdotly'    => ['surdotlybot'],
            'serpminer'   => ['serpminerbot'],
            'agentdata'   => ['agentdatabot'],
            // Social / messaging
            'twitter'  => ['twitterbot'],
            'linkedin' => ['linkedinbot'],
            'telegram' => ['telegrambot'],
            'whatsapp' => ['whatsapp'],
            'skype'    => ['skypeuripreview'],
            // Archival / web intel
            'archive'              => ['archive.org', 'heritrix'],
            'slurp'                => ['slurp'],
            'netcraft'             => ['netcraftsurveyagent'],
            'bitsight'             => ['bitsightbot'],
            'recorded_future'      => ['recordedfuture'],
            'internet_measurement' => ['internetmeasurement'],
            'pandalytics'          => ['pandalytics'],
            // Uptime monitors
            'pingdom'     => ['pingdom'],
            'uptimerobot' => ['uptimerobot'],
            'statuscake'  => ['statuscake'],
            'site24x7'    => ['site24x7'],
            'newrelic'    => ['newrelicpinger'],
            // WordPress / CMS scanners
            'wpbot'       => ['wpbot', 'wp-safe-scanner'],
            'cms_checker' => ['cms-checker'],
            // Generic frameworks / libraries
            'scrapy'    => ['scrapy/'],
            'python'    => ['python-requests', 'python-urllib', 'aiohttp'],
            'curl'      => ['curl'],
            'wget'      => ['wget'],
            'java'      => ['java/', 'okhttp', 'dalvik'],
            'go'        => ['go-http-client', 'req/v3'],
            'nodejs'    => ['node-fetch', 'httpx', 'axios', 'got '],
            'web_fetch' => ['webfetchtool'],
            // Hostile scanners / CVE feeds
            'scanner'    => ['masscan', 'nmap', 'nikto', 'sqlmap', 'wpscan', 'dirbuster', 'gobuster', 'zgrab', 'censys', 'shodan', 'shadow', 'l9scan', 'intelx', 'visionheight', 'palo alto networks', 'lrvl-livewire', 'root_evidence', 'ev-crawler'],
            'httpclient' => ['httpclient', 'lwp-'],
        ];
    }

    /**
     * Brand => category (4 buckets). Mirrors brandPatterns().
     *
     * @return array<string, string>
     */
    public static function categoryMap(): array
    {
        return [
            // Search engines — being indexed here drives SEO ranking
            'google'     => 'seo',
            'bing'       => 'seo',
            'baidu'      => 'seo',
            'sogou'      => 'seo',
            'shenma'     => 'seo',
            'bytespider' => 'seo',
            'petalbot'   => 'seo',
            'duckduckgo' => 'seo',
            'yandex'     => 'seo',
            'spider360'  => 'seo',
            'coccoc'     => 'seo',
            'slurp'      => 'seo',

            // AI / LLM training & AI search — being cited here drives GEO
            'openai'     => 'geo',
            'anthropic'  => 'geo',
            'perplexity' => 'geo',
            'ccbot'      => 'geo',
            'meta'       => 'geo',
            'you'        => 'geo',
            'amazon'     => 'geo',
            'applebot'   => 'geo',
            'ai_index'   => 'geo',

            // Friendly bots: SEO tools, monitors, social previews, archive
            'googleother'          => 'other',
            'google_inspection'    => 'other',
            'ahrefs'               => 'other',
            'semrush'              => 'other',
            'mj12'                 => 'other',
            'dotbot'               => 'other',
            'exabot'               => 'other',
            'serpstat'             => 'other',
            'seranking'            => 'other',
            'dataforseo'           => 'other',
            'brokenlinks'          => 'other',
            'velen'                => 'other',
            'webtrackr'            => 'other',
            'surdotly'             => 'other',
            'serpminer'            => 'other',
            'agentdata'            => 'other',
            'netcraft'             => 'other',
            'bitsight'             => 'other',
            'recorded_future'      => 'other',
            'internet_measurement' => 'other',
            'pandalytics'          => 'other',
            'pingdom'              => 'other',
            'uptimerobot'          => 'other',
            'statuscake'           => 'other',
            'site24x7'             => 'other',
            'newrelic'             => 'other',
            'twitter'              => 'other',
            'linkedin'             => 'other',
            'telegram'             => 'other',
            'whatsapp'             => 'other',
            'skype'                => 'other',
            'archive'              => 'other',

            // Suspicious: scanners + generic frameworks + unknown
            'scanner'     => 'suspicious',
            'wpbot'       => 'suspicious',
            'cms_checker' => 'suspicious',
            'scrapy'      => 'suspicious',
            'python'      => 'suspicious',
            'curl'        => 'suspicious',
            'wget'        => 'suspicious',
            'java'        => 'suspicious',
            'go'          => 'suspicious',
            'nodejs'      => 'suspicious',
            'web_fetch'   => 'suspicious',
            'httpclient'  => 'suspicious',
            'unknown'     => 'suspicious',

            // Behaviour-detected bots: no UA keyword matched, but the request
            // pattern gave the crawler away (see BotBehaviorClassifier).
            'behavior' => 'suspicious',

            // Second-pass detections that need no UA keyword either: entry
            // through a non-allowlisted vhost, and a burst of cookie-less
            // first hits all wearing one stale browser major version.
            'host'     => 'suspicious',
            'ua_burst' => 'suspicious',
        ];
    }

    /**
     * Classify a user agent. Returns a 3-tuple used by every bot write path.
     *
     * @param  string|null  $userAgent
     * @return array{is_bot: bool, brand: string, category: string}
     */
    public static function classify(?string $userAgent): array
    {
        $ua = strtolower(trim((string) $userAgent));

        if ($ua === '') {
            return ['is_bot' => true, 'brand' => 'unknown', 'category' => 'suspicious'];
        }

        // Pass 1 — specific brand patterns win.
        foreach (self::brandPatterns() as $brand => $patterns) {
            foreach ($patterns as $pattern) {
                if (str_contains($ua, $pattern)) {
                    return ['is_bot' => true, 'brand' => $brand, 'category' => self::categoryOf($brand)];
                }
            }
        }

        // Pass 2 — generic bot keywords, brand unknown.
        foreach (self::GENERIC_BOT_PATTERNS as $pattern) {
            if (str_contains($ua, $pattern)) {
                return ['is_bot' => true, 'brand' => 'unknown', 'category' => 'suspicious'];
            }
        }

        return ['is_bot' => false, 'brand' => '', 'category' => ''];
    }

    public static function isBot(?string $userAgent): bool
    {
        return self::classify($userAgent)['is_bot'];
    }

    public static function categoryOf(string $brand): string
    {
        return self::categoryMap()[$brand] ?? 'suspicious';
    }

    /**
     * Inline CSS for each bot category badge. Bootstrap 5 has no purple, so
     * we ship a raw background-color per category.
     *
     * @return array<string, string>
     */
    public static function categoryStyleMap(): array
    {
        return [
            'seo'        => 'background:#0d6efd;',
            'geo'        => 'background:#6f42c1;',
            'other'      => 'background:#6c757d;',
            'suspicious' => 'background:#dc3545;',
        ];
    }

    public static function categoryColor(string $category): string
    {
        return self::categoryStyleMap()[$category] ?? 'background:#6c757d;';
    }

    public static function categoryLabel(string $category): string
    {
        $key = 'panel/visit.bot_category_'.$category;

        return Lang::has($key) ? trans($key) : $category;
    }

    public static function categoryTip(string $category): string
    {
        $key = 'panel/visit.bot_category_'.$category.'_tip';

        return Lang::has($key) ? trans($key) : '';
    }

    public static function brandLabel(string $brand): string
    {
        $key = 'panel/visit.bot_brand_'.$brand;

        return Lang::has($key) ? trans($key) : $brand;
    }
}
