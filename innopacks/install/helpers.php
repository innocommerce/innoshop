<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use InnoShop\Install\Repositories\LocaleRepo;

if (! function_exists('install_lang_path_codes')) {
    /**
     * Get all panel languages
     *
     * @return array
     */
    function install_lang_path_codes(): array
    {
        $packages = language_codes();

        $panelLangCodes = collect($packages)->filter(function ($code) {
            return file_exists(lang_path("{$code}/install"));
        })->toArray();

        return array_values($panelLangCodes);
    }
}

if (! function_exists('install_locales')) {
    /**
     * @return array
     * @throws Exception
     */
    function install_locales(): array
    {
        return LocaleRepo::getInstance()->getInstallLanguages();
    }
}