<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use HTMLPurifier;
use HTMLPurifier_Config;

class HtmlPurifyService
{
    /**
     * Fields that are known to contain rich HTML content and need purification.
     * Other fields (title, summary, meta_*) are plain text and will be stripped.
     */
    private const HTML_FIELDS = ['content', 'description'];

    /**
     * HTMLPurifier singleton instance.
     */
    private static ?HTMLPurifier $purifier = null;

    /**
     * Purify a single HTML string (rich content mode).
     * Allows safe HTML tags, removes scripts, event handlers, etc.
     */
    public static function clean(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return self::getPurifier()->purify($html);
    }

    /**
     * Strip all HTML tags — for fields that should be plain text.
     */
    public static function strip(string $html): string
    {
        if ($html === '') {
            return '';
        }

        return strip_tags($html);
    }

    /**
     * Purify an array of translation data.
     * - HTML fields (content, description) → clean with HTMLPurifier
     * - Other fields → strip all tags
     */
    public static function purifyTranslation(array $translation): array
    {
        foreach ($translation as $key => $value) {
            if ($key === 'locale' || ! is_string($value)) {
                continue;
            }

            if (in_array($key, self::HTML_FIELDS)) {
                $translation[$key] = self::clean($value);
            } else {
                $translation[$key] = self::strip($value);
            }
        }

        return $translation;
    }

    /**
     * Get or create the HTMLPurifier singleton.
     */
    private static function getPurifier(): HTMLPurifier
    {
        if (self::$purifier !== null) {
            return self::$purifier;
        }

        $config = HTMLPurifier_Config::createDefault();

        // Encoding
        $config->set('Core.Encoding', 'UTF-8');

        // Enable id attribute support
        $config->set('Attr.EnableID', true);

        // Do not add DOCTYPE/html/body wrappers
        $config->set('HTML.Doctype', 'HTML 4.01 Transitional');
        $config->set('HTML.AllowedElements', self::getAllowedElements());
        $config->set('HTML.AllowedAttributes', self::getAllowedAttributes());

        // Auto-format
        $config->set('AutoFormat.AutoParagraph', false);
        $config->set('AutoFormat.RemoveEmpty', false);
        $config->set('AutoFormat.RemoveSpansWithoutAttributes', true);

        // URI: prevent javascript: and data: URIs
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true, 'tel' => true]);

        // Safe iframe — disabled for maximum safety
        $config->set('HTML.SafeIframe', false);
        $config->set('URI.SafeIframeRegexp', '');

        // Cache: use serializer cache for performance
        try {
            $cachePath = storage_path('framework/htmlpurifier');
        } catch (\Throwable) {
            $cachePath = sys_get_temp_dir().'/innoshop_htmlpurifier';
        }
        if (! is_dir($cachePath)) {
            @mkdir($cachePath, 0755, true);
        }
        $config->set('Cache.SerializerPath', $cachePath);
        $config->set('Cache.SerializerPermissions', 0755);

        self::$purifier = new HTMLPurifier($config);

        return self::$purifier;
    }

    /**
     * Allowed HTML elements whitelist.
     */
    private static function getAllowedElements(): string
    {
        return implode(',', [
            // Structure
            'div', 'p', 'br', 'hr', 'blockquote',
            // Headings
            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
            // Text
            'span', 'a', 'strong', 'b', 'em', 'i', 'u', 's', 'del', 'ins',
            'sub', 'sup', 'abbr', 'cite', 'code', 'pre', 'small',
            // Lists
            'ul', 'ol', 'li', 'dl', 'dt', 'dd',
            // Tables
            'table', 'thead', 'tbody', 'tfoot', 'tr', 'th', 'td', 'caption', 'colgroup', 'col',
            // Media
            'img',
        ]);
    }

    /**
     * Allowed HTML attributes whitelist.
     */
    private static function getAllowedAttributes(): string
    {
        return implode(',', [
            // Global
            '*.class', '*.id', '*.title', '*.lang', '*.dir', '*.style',
            // Links
            'a.href', 'a.target', 'a.rel', 'a.name',
            // Images
            'img.src', 'img.alt', 'img.width', 'img.height',
            // Table
            'table.border', 'table.cellpadding', 'table.cellspacing', 'table.width',
            'th.colspan', 'th.rowspan', 'th.scope', 'th.width',
            'td.colspan', 'td.rowspan', 'td.width',
            // Semantic
            // Details
            // Abbr
            'abbr.title',
        ]);
    }
}
