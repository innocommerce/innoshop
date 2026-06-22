<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

class RobotsService extends BaseService
{
    /**
     * Generate robots.txt content.
     * If custom rules are set, use them as the complete file content.
     * Otherwise, generate the default with Sitemap declaration.
     *
     * @return string
     */
    public function generate(): string
    {
        $customRules = trim((string) system_setting('robots_custom_rules', ''));
        if ($customRules !== '') {
            return $customRules."\n";
        }

        return $this->generateDefault();
    }

    /**
     * Generate default robots.txt content (always, ignoring custom rules).
     *
     * @return string
     */
    public function generateDefault(): string
    {
        $lines   = [];
        $lines[] = 'User-agent: *';
        $lines[] = 'Allow: /';
        $lines[] = 'Disallow: /'.panel_name().'/';
        $lines[] = 'Disallow: /api/';
        $lines[] = '';
        $lines[] = 'Sitemap: '.url('/sitemap.xml');

        return implode("\n", $lines)."\n";
    }

    /**
     * Write robots.txt to public directory.
     *
     * @return void
     */
    public function writeFile(): void
    {
        file_put_contents(public_path('robots.txt'), "\xEF\xBB\xBF".$this->generate());
    }
}
