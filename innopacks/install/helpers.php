<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use InnoShop\Install\Repositories\LocaleRepo;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

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

if (! function_exists('current_install_locale_code')) {
    /**
     * @return string
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    function current_install_locale_code(): string
    {
        try {
            $defaultLocale = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? 'en', 0, 2);
            $defaultLocale = ($defaultLocale == 'zh' ? 'zh_cn' : $defaultLocale);

            return (string) request()->get('locale', $defaultLocale);
        } catch (Exception $e) {
            return 'en';
        }
    }
}

if (! function_exists('current_install_locale')) {
    /**
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    function current_install_locale(): array
    {
        $locale = current_install_locale_code();
        foreach (install_locales() as $item) {
            if ($item['code'] == $locale) {
                return $item;
            }
        }

        return install_locales()[0];
    }
}
