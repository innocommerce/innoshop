<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Handlers;

use Exception;

/**
 * A utility class for handling translations in multilingual models
 *
 * This class provides methods to process translations with configurable
 * auto-fill options for multilingual fields. It abstracts translation handling
 * logic that can be reused across different repositories.
 */
class TranslationHandler
{
    /**
     * Process translations with configurable auto-fill options
     *
     * This method handles the following features:
     * - Auto-filling empty fields with default language values when enabled
     * - Auto-populating related fields based on field mapping (e.g., title to meta fields)
     * - Filtering translations by enabled locales
     *
     * @param  array  $translations  The raw translations data
     * @param  array  $fieldMap  Field mapping for auto-fill ['source' => ['target1', 'target2']]
     * @param  array  $options  Additional options
     * @return array Processed translations ready to be stored
     * @throws Exception
     */
    public static function process(array $translations, array $fieldMap = [], array $options = []): array
    {
        if (empty($translations)) {
            return [];
        }

        // Get settings
        $fillLang = $options['fill_lang'] ?? system_setting('auto_fill_lang', false);
        $fillTDK  = $options['fill_tdk']  ?? system_setting('title_to_tdk', false);

        // Get default translation
        $defaultTranslation = self::getDefaultTranslation($translations);
        $translationItems   = [];

        foreach ($translations as $translation) {
            if (! in_array($translation['locale'], enabled_locale_codes())) {
                continue;
            }

            $result = ['locale' => $translation['locale']];

            // Process each field in the translation
            foreach ($translation as $key => $value) {
                if ($key === 'locale') {
                    continue;
                }

                // When field is empty and auto-fill is enabled, try to use default translation
                if (empty($value) && $fillLang && isset($defaultTranslation[$key])) {
                    $result[$key] = $defaultTranslation[$key];
                } else {
                    $result[$key] = $value ?? '';
                }
            }

            // Apply field mapping for title/name to other fields (TDK fields)
            if ($fillTDK && ! empty($fieldMap)) {
                foreach ($fieldMap as $source => $targets) {
                    if (! empty($result[$source])) {
                        foreach ($targets as $target) {
                            // Only fill empty target fields with source value
                            if (empty($result[$target])) {
                                $result[$target] = $result[$source];
                            }
                        }
                    }
                }
            }

            $translationItems[] = $result;
        }

        return $translationItems;
    }

    /**
     * Get the default translation from the translations array
     *
     * Retrieves the translation for the system's default locale code.
     * Used primarily for auto-filling empty fields in other languages.
     *
     * @param  array  $translations  The translations array
     * @return array The default translation
     * @throws Exception If default translation cannot be found
     */
    public static function getDefaultTranslation(array $translations): array
    {
        if (empty($translations)) {
            throw new Exception('Translations cannot be empty');
        }

        $localeCode = setting_locale_code();
        foreach ($translations as $translation) {
            if ($translation['locale'] == $localeCode) {
                return $translation;
            }
        }

        throw new Exception('Default translation not found');
    }
}
