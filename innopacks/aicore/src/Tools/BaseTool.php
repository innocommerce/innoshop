<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use InnoShop\Aicore\Contracts\ToolInterface;

abstract class BaseTool implements ToolInterface
{
    /**
     * SEO TDK fields shared by all translatable content entities.
     */
    public const META_FIELDS = ['meta_title', 'meta_description', 'meta_keywords'];

    /**
     * Mutating tools (create/update/ship/...) override this to true.
     */
    protected bool $write = false;

    public function isWrite(): bool
    {
        return $this->write;
    }

    /**
     * Description in the current app locale, falling back to the English
     * description when no translation exists for this tool yet.
     */
    public function localizedDescription(): string
    {
        $key        = 'aicore::tool.desc_'.$this->name();
        $translated = __($key);

        return $translated !== $key ? $translated : $this->description();
    }

    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => new \stdClass,
        ];
    }

    public function requiredPermission(): ?string
    {
        return null;
    }

    /**
     * Build a translations schema keyed by each enabled locale so the AI sees
     * exactly which locale keys exist and what fields each accepts. Nested
     * `additionalProperties` is stripped by laravel/ai's SchemaNormalizer, so
     * per-locale `properties` is the only structure that reaches the model.
     *
     * @param  array  $fields  Field => description map for each locale entry.
     */
    protected static function translationsSchema(array $fields, string $description): array
    {
        $properties = [];
        foreach ($fields as $field => $desc) {
            $properties[$field] = ['type' => 'string', 'description' => $desc];
        }

        $entry = ['type' => 'object', 'properties' => $properties];

        $byLocale = [];
        foreach (enabled_locale_codes() as $code) {
            $byLocale[$code] = $entry;
        }

        return [
            'type'        => 'object',
            'description' => $description,
            'properties'  => $byLocale,
        ];
    }
}
