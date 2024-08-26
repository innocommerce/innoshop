<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;
use InnoShop\Panel\Repositories\LocaleRepo;

if (! function_exists('panel_name')) {
    /**
     * Admin panel name
     *
     * @return string
     */
    function panel_name(): string
    {
        return system_setting('panel_name', 'panel') ?: 'panel';
    }
}

if (! function_exists('panel_locales')) {
    /**
     * Get available locales
     *
     * @return array
     * @throws Exception
     */
    function panel_locales(): array
    {
        return LocaleRepo::getInstance()->getPanelLanguages();
    }
}

if (! function_exists('panel_locale_code')) {
    /**
     * Get panel locale code
     *
     * @return string
     * @throws Exception
     */
    function panel_locale_code(): string
    {
        return current_admin()->locale ?? panel_session_locale();
    }
}

if (! function_exists('panel_session_locale')) {
    /**
     * Get panel locale code from session
     *
     * @return string
     * @throws Exception
     */
    function panel_session_locale(): string
    {
        return session('panel_locale', setting_locale_code());
    }
}

if (! function_exists('current_panel_locale')) {
    /**
     * Get current locale code.
     *
     * @return array
     * @throws Exception
     */
    function current_panel_locale(): array
    {
        return LocaleRepo::getInstance()->getLocaleByCode(panel_locale_code());
    }
}

if (! function_exists('panel_lang_path_codes')) {
    /**
     * Get all panel languages
     *
     * @return array
     */
    function panel_lang_path_codes(): array
    {
        $packages = language_codes();

        $panelLangCodes = collect($packages)->filter(function ($code) {
            return file_exists(lang_path("{$code}/panel"));
        })->toArray();

        return array_values($panelLangCodes);
    }
}

if (! function_exists('panel_trans')) {
    /**
     * @param  $key
     * @param  array  $replace
     * @param  $locale
     * @return mixed
     */
    function panel_trans($key = null, array $replace = [], $locale = null): mixed
    {
        return trans('panel/'.$key, $replace, $locale);
    }
}

if (! function_exists('panel_route')) {
    /**
     * Get backend panel route
     *
     * @param  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     */
    function panel_route($name, mixed $parameters = [], bool $absolute = true): string
    {
        try {
            $panelName = panel_name();

            return route($panelName.'.'.$name, $parameters, $absolute);
        } catch (\Exception $e) {
            return route($panelName.'.dashboard.index');
        }

    }
}

if (! function_exists('current_admin')) {
    /**
     * get current admin user.
     */
    function current_admin(): ?Authenticatable
    {
        return auth('admin')->user();
    }
}

if (! function_exists('is_admin')) {
    /**
     * Check if current is admin panel
     * @return bool
     */
    function is_admin(): bool
    {
        $adminName = panel_name();
        $uri       = request()->getRequestUri();
        if (Str::startsWith($uri, "/{$adminName}")) {
            return true;
        }

        return false;
    }
}
