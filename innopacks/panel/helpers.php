<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Illuminate\Support\Str;
use InnoShop\Common\Support\EntityLinkPayload;
use InnoShop\Panel\Repositories\LocaleRepo;
use InnoShop\Panel\Services\TranslatorService;

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

if (! function_exists('panel_locale_direction')) {
    /**
     * Get locale direction for panel admin.
     *
     * @return string
     * @throws Exception
     */
    function panel_locale_direction(): string
    {
        $localeCode = panel_locale_code();
        $rtlCodes   = array_keys(InnoShop\Common\Repositories\LocaleRepo::getRtlLanguages());

        return in_array($localeCode, $rtlCodes) ? 'rtl' : 'ltr';
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
        $panelName = panel_name();
        try {
            return route($panelName.'.'.$name, $parameters, $absolute);
        } catch (Exception $e) {
            return route($panelName.'.dashboard.index');
        }

    }
}

if (! function_exists('current_admin')) {
    /**
     * get current admin user.
     */
    function current_admin(): mixed
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

if (! function_exists('dashboard_url')) {
    /**
     * Get dashboard url
     * like https://www.innoshop.com/install/dashboard.jpg?edition=community&version=1.0.0&build_date=20250909
     *
     * @return string
     */
    function dashboard_url(): string
    {
        $params = [
            'base_url'   => panel_route('home.index'),
            'edition'    => config('innoshop.edition'),
            'version'    => config('innoshop.version'),
            'build_date' => config('innoshop.build'),
        ];
        $urlParams = http_build_query($params);

        return config('innoshop.api_url').'/install/dashboard.jpg?'.$urlParams;
    }
}

if (! function_exists('default_locale_class')) {
    /**
     * Get default locale class name for panel admin.
     * @param  $localeCode
     * @return string
     */
    function default_locale_class($localeCode): string
    {
        return is_setting_locale($localeCode) ? 'border border-2 border-danger-subtle ' : '';
    }
}

if (! function_exists('has_translator')) {
    /**
     * Check if the translator is enabled.
     *
     * @return bool
     */
    function has_translator(): bool
    {
        try {
            return TranslatorService::getTranslator() !== null;
        } catch (Exception $e) {
            return false;
        }
    }
}

if (! function_exists('has_set_value')) {
    /**
     * Verify if any fields in the current parameters have been assigned a value.
     *
     * @param  $parameters
     * @return bool
     */
    function has_set_value($parameters): bool
    {
        $ignoreList = ['page', 'per_page', 'sort', 'order'];
        foreach ($parameters as $key => $value) {
            if (in_array($key, $ignoreList)) {
                continue;
            }
            if (! is_null($value)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('panel_link_parse')) {
    /**
     * InnoLinkPicker: {@see EntityLinkPayload::normalize()} plus DB enrichment (same as {@see entity_link_enrich()}).
     * Full storefront row + href: {@see entity_link_display()}; URL only (no DB): {@see entity_link_url()}.
     *
     * @param  array|string|null  $stored
     * @return array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}
     */
    function panel_link_parse(array|string|null $stored): array
    {
        return entity_link_enrich(entity_link_normalize($stored));
    }
}

if (! function_exists('panel_link_resolve')) {
    /**
     * @param  class-string  $modelClass
     */
    function panel_link_resolve(string $modelClass, string $value, array $with = []): ?object
    {
        return entity_link_resolve($modelClass, $value, $with);
    }
}

if (! function_exists('panel_link_enrich')) {
    /**
     * @param  array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}  $row
     * @return array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}
     */
    function panel_link_enrich(array $row): array
    {
        return entity_link_enrich($row);
    }
}

if (! function_exists('panel_link_finalize')) {
    /**
     * @param  array{type?: string, value?: mixed, entity_label?: string, link?: string, entity_image?: string, entity_price?: string}  $row
     * @param  array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}  $defaults
     * @return array{type: string, value: string, entity_label: string, link: string, entity_image: string, entity_price: string}
     */
    function panel_link_finalize(array $row, array $defaults): array
    {
        return entity_link_enrich(EntityLinkPayload::mergeAndCast($row, $defaults));
    }
}

if (! function_exists('panel_locale_field_config')) {
    /**
     * JSON-ready config for {@see InnoPanelLocaleText}: enabled locales, panel language, storefront default language.
     * Merge into panel view data so plugins can reuse the same multi-locale editor.
     *
     * @return array{locale_list: array<int, array{code: string, name: string, image: string}>, locale_codes: array<int, string>, panel_locale_code: string, fallback_locale_code: string}
     */
    function panel_locale_field_config(): array
    {
        $localeList = locales()->map(static function ($locale) {
            return [
                'code'  => $locale->code,
                'name'  => $locale->name,
                'image' => $locale->image ? image_origin($locale->image) : asset('images/flag/'.$locale->code.'.png'),
            ];
        })->values()->all();

        return [
            'locale_list'          => $localeList,
            'locale_codes'         => locales()->pluck('code')->values()->all(),
            'panel_locale_code'    => panel_locale_code(),
            'fallback_locale_code' => setting_locale_code(),
        ];
    }
}

if (! function_exists('sorted_locales')) {
    /**
     * Get locales sorted by priority: panel locale → fallback locale → rest.
     */
    function sorted_locales()
    {
        $panelLocale    = panel_locale_code();
        $fallbackLocale = setting_locale_code();

        return locales()->sortBy(function ($locale) use ($panelLocale, $fallbackLocale) {
            if ($locale->code === $panelLocale) {
                return 0;
            }
            if ($locale->code === $fallbackLocale) {
                return 1;
            }

            return 2;
        })->values();
    }
}

if (! function_exists('locale_field_data')) {
    /**
     * Extract translation values for a specific field from a model.
     * Returns [localeCode => value] array, with old() input support.
     *
     * @param  mixed  $model  Model with Translatable trait (or null for new)
     * @param  string  $fieldName  Field name to extract
     * @return array<string, string>
     */
    function locale_field_data($model, string $fieldName): array
    {
        $data = [];
        foreach (locales() as $locale) {
            $code  = $locale->code;
            $value = old("translations.{$code}.{$fieldName}");
            if ($value === null && $model) {
                $value = $model->translate($code, $fieldName);
            }
            $data[$code] = (string) ($value ?? '');
        }

        return $data;
    }
}

if (! function_exists('json_field_data')) {
    /**
     * Extract translation values for a specific JSON field from a model.
     * Returns [localeCode => value] array, with old() input support.
     * For models using JSON columns (like Option) instead of translation tables.
     *
     * @param  mixed  $model  Model with JSON casted field (or null for new)
     * @param  string  $fieldName  Field name to extract (e.g. 'name', 'description')
     * @return array<string, string>
     */
    function json_field_data($model, string $fieldName): array
    {
        $data  = [];
        $field = $model ? ($model->{$fieldName} ?? []) : [];

        foreach (locales() as $locale) {
            $code  = $locale->code;
            $value = old("translations.{$code}.{$fieldName}");
            if ($value === null) {
                $value = $field[$code] ?? '';
            }
            $data[$code] = (string) ($value ?? '');
        }

        return $data;
    }
}
