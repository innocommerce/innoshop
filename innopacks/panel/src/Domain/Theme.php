<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace InnoShop\Panel\Domain;

use Illuminate\Support\Arr;

/**
 * Theme Domain Object
 *
 * Represents a theme in the system with its configuration and status
 */
class Theme
{
    /**
     * Create a new Theme instance
     *
     * @param  string  $code  Theme unique identifier (must be lowercase)
     * @param  array  $names  Theme name in different languages
     * @param  array  $descriptions  Theme description in different languages
     * @param  string  $version  Theme version number
     * @param  string  $icon  Theme icon path
     * @param  array  $author  Theme author information
     * @param  string  $path  Theme directory path
     * @param  bool  $hasDemo  Whether theme has demo data
     * @param  string|null  $demoPath  Path to demo data file
     * @param  bool  $selected  Whether theme is currently selected
     * @param  string  $preview  Theme preview image path
     */
    public function __construct(
        public readonly string $code,
        public readonly array $names,
        public readonly array $descriptions,
        public readonly string $version,
        public readonly string $icon,
        public readonly array $author,
        public readonly string $path,
        public readonly bool $hasDemo = false,
        public readonly ?string $demoPath = null,
        public readonly bool $selected = false,
        public readonly string $preview = '',
    ) {}

    /**
     * Create a Theme instance from array data
     *
     * @param  array  $data  Theme configuration data
     * @return self
     */
    public static function fromArray(array $data): self
    {
        return new self(
            code: $data['code'],
            names: $data['name'],          // Changed from names to name to match config
            descriptions: $data['description'], // Changed from descriptions to description
            version: $data['version'],
            icon: $data['icon'],
            author: $data['author'],
            path: $data['path'],
            hasDemo: $data['has_demo'] ?? false,
            demoPath: $data['demo_path'] ?? null,
            selected: $data['selected'] ?? false,
            preview: $data['preview'] ?? '',
        );
    }

    /**
     * Get theme name in specified locale
     *
     * @param  string|null  $locale  Language code (e.g., 'zh-cn', 'en')
     * @return string Theme name in requested locale or first available
     */
    public function getName(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->names[$locale] ?? Arr::first($this->names);
    }

    /**
     * Get theme description in specified locale
     *
     * @param  string|null  $locale  Language code (e.g., 'zh-cn', 'en')
     * @return string Theme description in requested locale or first available
     */
    public function getDescription(?string $locale = null): string
    {
        $locale = $locale ?: app()->getLocale();

        return $this->descriptions[$locale] ?? Arr::first($this->descriptions);
    }

    /**
     * Convert theme to array representation
     *
     * @return array Theme data with current locale and original multilingual data
     */
    public function toArray(): array
    {
        $locale = app()->getLocale();

        return [
            'code'         => $this->code,
            'name'         => $this->getName($locale),
            'names'        => $this->names,
            'description'  => $this->getDescription($locale),
            'descriptions' => $this->descriptions,
            'version'      => $this->version,
            'icon'         => $this->icon,
            'author'       => $this->author,
            'path'         => $this->path,
            'has_demo'     => $this->hasDemo,
            'demo_path'    => $this->demoPath,
            'selected'     => $this->selected,
            'preview'      => $this->preview,
        ];
    }
}
