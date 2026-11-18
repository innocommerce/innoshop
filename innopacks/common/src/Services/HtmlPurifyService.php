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

        // HTML5 <video>/<source> and <style> support. Must run AFTER every $config->set()
        // call: requesting the definition finalizes the config, and any set()
        // that follows throws "Cannot set directive after finalization".
        // maybeGetRawHTMLDefinition() is the official cache-safe entry point
        // (returns null when a cached definition already exists). HTMLPurifier
        // 4.x ships an HTML4 doctype with no native definition for these tags,
        // so register them as custom elements (also listed in AllowedElements /
        // AllowedAttributes above).
        $config->set('HTML.DefinitionID', 'innoshop_richtext_html5');
        $config->set('HTML.DefinitionRev', 2);
        if ($def = $config->maybeGetRawHTMLDefinition()) {
            self::registerCustomElements($def);
        }

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
            // HTML5 media (站内视频)
            'video', 'source',
            // 页内样式（后台可信用户，与 TinyMCE custom_elements 保持一致）
            'style',
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
            // HTML5 video (站内视频)
            'video.src', 'video.type', 'video.width', 'video.height', 'video.poster',
            'video.preload', 'video.controls', 'video.muted', 'video.loop', 'video.autoplay', 'video.playsinline',
            // <source> child of <video>
            'source.src', 'source.type',
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

    /**
     * Register HTML5 <video>/<source>/<style> element definitions.
     * HTMLPurifier 4.x only knows HTML4 tags natively, so without this the
     * tags are stripped during purification even if listed in AllowedElements.
     */
    private static function registerCustomElements(\HTMLPurifier_HTMLDefinition $def): void
    {
        // <video>: block-level, may wrap <source> children and fallback text.
        $def->addElement('video', 'Block', 'Flow', 'Common', [
            'src'         => 'URI',
            'type'        => 'Text',
            'width'       => 'Length',
            'height'      => 'Length',
            'poster'      => 'URI',
            'preload'     => 'Enum#auto,metadata,none',
            'controls'    => 'Bool',
            'muted'       => 'Bool',
            'loop'        => 'Bool',
            'autoplay'    => 'Bool',
            'playsinline' => 'Bool',
        ]);

        // <source>: empty element, valid inside <video>/<audio>.
        $def->addElement('source', 'Block', 'Empty', 'Common', [
            'src'  => 'URI',
            'type' => 'Text',
        ]);

        // <style>: raw-text element (same #PCDATA model HTMLPurifier uses for
        // <script> in its Scripting module). Only reached by trusted panel
        // users; front-end rendering is a deliberate theme choice.
        $def->addElement('style', 'Block', 'Optional: #PCDATA', 'Common', [
            'type' => 'Enum#text/css',
        ]);
    }
}
