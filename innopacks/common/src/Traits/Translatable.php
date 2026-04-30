<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Traits;

use Exception;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait Translatable
{
    /**
     * 设置 Description model
     * @return string
     */
    public function getDescriptionModelClass(): string
    {
        return self::class.'\Translation';
    }

    /**
     * Define translations relationship
     *
     * @return HasMany
     */
    public function translations(): HasMany
    {
        $class = $this->getDescriptionModelClass();

        return $this->hasMany($class, $this->getForeignKey(), $this->getKeyName());
    }

    /**
     * Locale translation object
     *
     * @return mixed
     * @throws Exception
     */
    public function translation(): mixed
    {
        $class = $this->getDescriptionModelClass();

        return $this->hasOne($class, $this->getForeignKey(), $this->getKeyName())->where('locale', locale_code());
    }

    /**
     * Translate field by locale
     *
     * @param  $locale
     * @param  $field
     * @return string
     */
    public function translate($locale, $field): string
    {
        return $this->translations->where('locale', $locale)->first()?->{$field} ?? '';
    }

    /**
     * Get translated name.
     *
     * @param  string  $field
     * @return string
     */
    public function translatedName(string $field = 'name'): string
    {
        return $this->translation?->{$field} ?? '';
    }

    /**
     * Get fallback name.
     * 1. Current locale -> 2. System default locale -> 3. Any available locale
     *
     * @param  string  $field
     * @return string
     */
    public function fallbackName(string $field = 'name'): string
    {
        $translatedName = $this->translatedName($field);
        if ($translatedName) {
            return $translatedName;
        }

        $defaultName = $this->translate(setting_locale_code(), $field);
        if ($defaultName) {
            return $defaultName;
        }

        return $this->translations->first()?->{$field} ?? '';
    }
}
