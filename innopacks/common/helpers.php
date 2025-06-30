<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Barryvdh\Debugbar\Facades\Debugbar;
use Detection\Exception\MobileDetectException;
use Detection\MobileDetect;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use InnoShop\Common\Libraries\ApiHook;
use InnoShop\Common\Libraries\Currency;
use InnoShop\Common\Libraries\ViewHook;
use InnoShop\Common\Libraries\Weight;
use InnoShop\Common\Repositories\CurrencyRepo;
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Common\Services\ImageService;
use InnoShop\Common\Support\Registry;

if (! function_exists('load_settings')) {
    /**
     * Load all settings from table and set to laravel config
     *
     * @return void
     */
    function load_settings(): void
    {
        if (! installed()) {
            return;
        }

        if (config('inno')) {
            return;
        }

        $result = SettingRepo::getInstance()->groupedSettings();
        config(['inno' => $result]);
    }
}

if (! function_exists('setting')) {
    /**
     * Retrieve the values from the settings table for backend configurations
     *
     * @param  $key
     * @param  null  $default
     * @return mixed
     */
    function setting($key, $default = null): mixed
    {
        return config("inno.{$key}", $default);
    }
}

if (! function_exists('system_setting')) {
    /**
     * Get system settings
     *
     * @param  $key
     * @param  null  $default
     * @return mixed
     */
    function system_setting($key, $default = null): mixed
    {
        return setting("system.{$key}", $default);
    }
}

if (! function_exists('system_setting_locale')) {
    /**
     * Get system settings
     *
     * @param  $key
     * @param  null  $default
     * @return mixed
     */
    function system_setting_locale($key, $default = null): mixed
    {
        $localeCode = front_locale_code();

        return setting("system.{$key}.$localeCode", $default);
    }
}

if (! function_exists('locale_image')) {
    /**
     * Get locale image
     *
     * @param  string  $code
     * @return string
     * @throws Exception
     */
    function locale_image(string $code): string
    {
        $locale = locales()->where('code', $code)->first();

        return $locale ? $locale->image : '';
    }
}

if (! function_exists('locale_name')) {
    /**
     * Get locale name by code
     *
     * @param  string  $code
     * @return string
     * @throws Exception
     */
    function locale_name(string $code): string
    {
        $locale = locales()->where('code', $code)->first();

        return $locale ? $locale->name : $code;
    }
}

if (! function_exists('is_secure')) {
    /**
     * Check if current env is https
     *
     * @return bool
     */
    function is_secure(): bool
    {
        if (! empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
            return true;
        } elseif (! empty($_SERVER['HTTP_FRONT_END_HTTPS']) && strtolower($_SERVER['HTTP_FRONT_END_HTTPS']) !== 'off') {
            return true;
        } elseif (isset($_SERVER['SERVER_PORT']) && intval($_SERVER['SERVER_PORT']) === 443) {
            return true;
        } elseif (isset($_SERVER['REQUEST_SCHEME']) && strtolower($_SERVER['REQUEST_SCHEME']) === 'https') {
            return true;
        }

        return false;
    }
}

if (! function_exists('is_mobile')) {
    /**
     * Check if current is mobile by user agent.
     *
     * @return bool
     */
    function is_mobile(): bool
    {
        try {
            return (new MobileDetect)->isMobile();
        } catch (MobileDetectException $e) {
            return false;
        }
    }
}

if (! function_exists('is_wechat_official')) {
    /**
     * Check if current is WeChat official by user agent.
     *
     * @return bool
     */
    function is_wechat_official(): bool
    {
        $userAgent = request()->userAgent();
        if (str_contains($userAgent, 'MicroMessenger')) {
            return true;
        }

        return false;
    }
}

if (! function_exists('is_wechat_mini')) {
    /**
     * Check if current is WeChat official by user agent.
     *
     * @return bool
     */
    function is_wechat_mini(): bool
    {
        if (request()->header('platform') == 'miniprogram') {
            return true;
        }
        $userAgent = request()->userAgent();
        if (str_contains($userAgent, 'wxwork') || str_contains($userAgent, 'wxlite') || str_contains($userAgent, 'miniprogram')) {
            return true;
        }

        return false;
    }
}

if (! function_exists('is_app')) {
    /**
     * Check if current is APP by request.
     *
     * @return bool
     */
    function is_app(): bool
    {
        return (bool) request()->header('from_app', false);
    }
}

if (! function_exists('has_install_lock')) {
    /**
     * Check install lockfile.
     *
     * @return bool
     */
    function has_install_lock(): bool
    {
        return file_exists(storage_path('installed'));
    }
}

if (! function_exists('installed')) {
    /**
     * Check installed by DB connection.
     *
     * @return bool
     */
    function installed(): bool
    {
        try {
            if (Schema::hasTable('settings') && has_install_lock()) {
                return true;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());

            return false;
        }

        return false;
    }
}

if (! function_exists('inno_path')) {
    /**
     * Get innopack path
     *
     * @param  string  $path
     * @return string
     */
    function inno_path(string $path): string
    {
        return base_path("innopacks/{$path}");
    }
}

if (! function_exists('current_customer')) {
    /**
     * Get current customer.
     */
    function current_customer(): ?Authenticatable
    {
        return auth('customer')->user();
    }
}

if (! function_exists('current_customer_id')) {
    /**
     * Get current customer ID
     *
     * @return int
     */
    function current_customer_id(): int
    {
        $customer = current_customer();

        return $customer->id ?? 0;
    }
}

if (! function_exists('token_customer_id')) {
    /**
     * Get current customer ID
     *
     * @return int
     */
    function token_customer_id(): int
    {
        return request()->user()->id ?? 0;
    }
}

if (! function_exists('token_customer')) {
    /**
     * Get current customer ID
     *
     * @return mixed
     */
    function token_customer(): mixed
    {
        return request()->user();
    }
}

if (! function_exists('current_guest_id')) {
    /**
     * Get guest ID from session ID
     *
     * @return string
     */
    function current_guest_id(): string
    {
        return session()->getId();
    }
}

if (! function_exists('locales')) {
    /**
     * Get available locales
     *
     * @return mixed
     * @throws Exception
     */
    function locales(): mixed
    {
        return LocaleRepo::getInstance()->getActiveList();
    }
}

if (! function_exists('enabled_locale_codes')) {
    /**
     * Get available locale codes
     *
     * @return mixed
     * @throws Exception
     */
    function enabled_locale_codes(): mixed
    {
        return locales()->pluck('code')->toArray();
    }
}

if (! function_exists('setting_locale_code')) {
    /**
     * Get setting locale code.
     *
     * @return string
     */
    function setting_locale_code(): string
    {
        return system_setting('front_locale', config('app.locale', 'en'));
    }
}

if (! function_exists('is_setting_locale')) {
    /**
     * Check if setting locale.
     *
     * @param  $localeCode
     * @return string
     */
    function is_setting_locale($localeCode): string
    {
        return setting_locale_code() == $localeCode;
    }
}

if (! function_exists('language_codes')) {
    /**
     * 获取语言包列表
     * @return array
     */
    function language_codes(): array
    {
        $languageDir = lang_path();

        return array_values(array_diff(scandir($languageDir), ['..', '.', '.DS_Store']));
    }
}

if (! function_exists('front_locale_code')) {
    /**
     * Get current locale code.
     *
     * @return string
     */
    function front_locale_code(): string
    {
        return session('locale') ?? setting_locale_code();
    }
}

if (! function_exists('locale_code')) {
    /**
     * Get current locale code.
     *
     * @return string
     * @throws Exception
     */
    function locale_code(): string
    {
        $configLocale = config('app.locale');
        if (is_admin()) {
            $locale = current_admin()->locale ?? $configLocale;
            if (locales()->contains('code', $locale)) {
                return $locale;
            } else {
                return setting_locale_code();
            }
        }

        return session('locale', setting_locale_code());
    }
}

if (! function_exists('current_locale')) {
    /**
     * Get current locale code.
     *
     * @return mixed
     * @throws Exception
     */
    function current_locale(): mixed
    {
        return LocaleRepo::getInstance()->builder(['code' => front_locale_code()])->first();
    }
}

if (! function_exists('front_locale_direction')) {
    /**
     * Get locale direction for frontend.
     *
     * @return string
     */
    function front_locale_direction(): string
    {
        $localeCode = front_locale_code();
        $rtlCodes   = array_keys(LocaleRepo::getRtlLanguages());

        return in_array($localeCode, $rtlCodes) ? 'rtl' : 'ltr';
    }
}

if (! function_exists('front_lang_path_codes')) {
    /**
     * Get all panel languages
     *
     * @return array
     */
    function front_lang_path_codes(): array
    {
        $packages = language_codes();

        $panelLangCodes = collect($packages)->filter(function ($code) {
            return file_exists(lang_path("{$code}/front"));
        })->toArray();

        return array_values($panelLangCodes);
    }
}

if (! function_exists('pure_route_name')) {
    /**
     * @return string
     * @throws Exception
     */
    function pure_route_name(): string
    {
        $name = request()->route()->getName();

        return str_replace([locale_code().'.front.', 'front.'], '', $name);
    }
}

if (! function_exists('front_trans')) {
    /**
     * @param  $key
     * @param  array  $replace
     * @param  $locale
     * @return mixed
     */
    function front_trans($key = null, array $replace = [], $locale = null): mixed
    {
        return trans('front/'.$key, $replace, $locale);
    }
}

if (! function_exists('theme_trans')) {
    /**
     * @param  $key
     * @param  string  $theme
     * @param  array  $replace
     * @param  null  $locale
     * @return mixed
     */
    function theme_trans($key, string $theme = '', array $replace = [], $locale = null): mixed
    {
        if (empty($theme)) {
            $theme = system_setting('theme', 'default');
        }

        return trans("theme-$theme::$key", $replace, $locale);
    }
}

if (! function_exists('inno_view')) {
    /**
     * @param  null  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return mixed
     */
    function inno_view($view = null, array $data = [], array $mergeData = []): mixed
    {
        $hook = ViewHook::getInstance()->getHookName(debug_backtrace());

        if ($hook) {
            $data = fire_hook_filter($hook, $data);
        }

        return view($view, $data, $mergeData);
    }
}

if (! function_exists('debug_view')) {
    /**
     * @param  $params
     * @return mixed
     */
    function debug_view($params): mixed
    {
        return view('debug', ['data' => $params]);
    }
}

if (! function_exists('create_json_success')) {
    /**
     * @param  null  $data
     * @return mixed
     */
    function create_json_success($data = null): mixed
    {
        $hook = ApiHook::getInstance()->getHookName(debug_backtrace());
        if ($hook) {
            $data = fire_hook_filter($hook, $data);
        }

        return json_success(panel_trans('common.saved_success'), $data);
    }
}

if (! function_exists('read_json_success')) {
    /**
     * @param  null  $data
     * @return mixed
     */
    function read_json_success($data = null): mixed
    {
        $hook = ApiHook::getInstance()->getHookName(debug_backtrace());
        if ($hook) {
            $data = fire_hook_filter($hook, $data);
        }

        return json_success(panel_trans('common.read_success'), $data);
    }
}

if (! function_exists('update_json_success')) {
    /**
     * @param  null  $data
     * @return mixed
     */
    function update_json_success($data = null): mixed
    {
        $hook = ApiHook::getInstance()->getHookName(debug_backtrace());
        if ($hook) {
            $data = fire_hook_filter($hook, $data);
        }

        return json_success(panel_trans('common.updated_success'), $data);
    }
}

if (! function_exists('delete_json_success')) {
    /**
     * @param  null  $data
     * @return mixed
     */
    function delete_json_success($data = null): mixed
    {
        $hook = ApiHook::getInstance()->getHookName(debug_backtrace());
        if ($hook) {
            $data = fire_hook_filter($hook, $data);
        }

        return json_success(panel_trans('common.deleted_success'), $data);
    }
}

if (! function_exists('submit_json_success')) {
    /**
     * @param  null  $data
     * @return mixed
     */
    function submit_json_success($data = null): mixed
    {
        $hook = ApiHook::getInstance()->getHookName(debug_backtrace());
        if ($hook) {
            $data = fire_hook_filter($hook, $data);
        }

        return json_success(panel_trans('common.submitted_success'), $data);
    }
}

if (! function_exists('json_success')) {
    /**
     * @param  $message
     * @param  $data
     * @return mixed
     */
    function json_success($message, $data = null): mixed
    {
        if ($data instanceof Model) {
            $data = $data->toArray();
        }

        $json = [
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ];

        $debugBar = request()->has('bar');
        if ($debugBar) {
            return view('panel::debugbar', ['data' => $json]);
        }

        return response()->json($json);
    }
}

if (! function_exists('json_fail')) {
    /**
     * @param  $message
     * @param  $data
     * @param  int  $code
     * @return mixed
     */
    function json_fail($message, $data = null, int $code = 422): mixed
    {
        if ($data instanceof Model) {
            $data = $data->toArray();
        }

        $json = [
            'success' => false,
            'message' => $message,
            'data'    => $data,
        ];

        $debugBar = request()->has('bar');
        if ($debugBar) {
            return view('panel::debugbar', ['data' => $json]);
        }

        return response()->json($json, $code);
    }
}

if (! function_exists('image_resize')) {
    /**
     * Resize image
     *
     * @param  ?string  $image
     * @param  int  $width
     * @param  int  $height
     * @return string
     * @throws Exception
     */
    function image_resize(?string $image = '', int $width = 100, int $height = 100): string
    {
        if (Str::startsWith($image, 'http')) {
            return $image;
        }

        return (new ImageService((string) $image))->resize($width, $height);
    }
}

if (! function_exists('image_origin')) {
    /**
     * Get origin image
     *
     * @throws Exception
     */
    function image_origin($image)
    {
        if (Str::startsWith($image, 'http')) {
            return $image;
        }

        return (new ImageService($image))->originUrl();
    }
}

if (! function_exists('sub_string')) {
    /**
     * @param  $string
     * @param  int  $length
     * @param  string  $dot
     * @return string
     */
    function sub_string($string, int $length = 16, string $dot = '...'): string
    {
        $string    = (string) $string;
        $strLength = mb_strlen($string);
        if ($length <= 0) {
            return $string;
        } elseif ($strLength <= $length) {
            return $string;
        }

        return mb_substr($string, 0, $length).$dot;
    }
}

if (! function_exists('create_directories')) {
    /**
     * Create directories recursively
     *
     * @param  $directoryPath
     * @return void
     */
    function create_directories($directoryPath): void
    {
        $ds   = DIRECTORY_SEPARATOR;
        $path = '';

        $directoryPath = str_replace(['/', '\\'], $ds, $directoryPath);
        if (substr($directoryPath, 0, 1) === $ds) {
            $path = $ds;
        }

        $directories = explode($ds, $directoryPath);
        foreach ($directories as $directory) {
            if ($directory === '') {
                continue;
            }

            if ($path === '' || $path === $ds) {
                $path .= $directory;
            } else {
                $path .= $ds.$directory;
            }

            if (! is_dir($path)) {
                if (! @mkdir($path, 0755, true) && ! is_dir($path)) {
                    throw new RuntimeException(sprintf('Directory "%s" was not created', $path));
                }
            }
        }
    }
}

if (! function_exists('front_route')) {
    /**
     * Get frontend route
     *
     * @param  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     * @throws Exception
     */
    function front_route($name, mixed $parameters = [], bool $absolute = true): string
    {
        if (hide_url_locale()) {
            return route('front.'.$name, $parameters, $absolute);
        }

        return route(front_locale_code().'.front.'.$name, $parameters, $absolute);
    }
}

if (! function_exists('front_root_route')) {
    /**
     * Get frontend route
     *
     * @param  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     * @throws Exception
     */
    function front_root_route($name, mixed $parameters = [], bool $absolute = true): string
    {
        return route('front.'.$name, $parameters, $absolute);
    }
}

if (! function_exists('has_front_route')) {
    /**
     * Check frontend route exist.
     *
     * @param  $name
     * @return bool
     * @throws Exception
     */
    function has_front_route($name): bool
    {
        if (hide_url_locale()) {
            $route = 'front.'.$name;
        } else {
            $route = front_locale_code().'.front.'.$name;
        }

        return Route::has($route);
    }
}

if (! function_exists('account_route')) {
    /**
     * Get account route
     *
     * @param  $name
     * @param  mixed  $parameters
     * @param  bool  $absolute
     * @return string
     * @throws Exception
     */
    function account_route($name, mixed $parameters = [], bool $absolute = true): string
    {
        if (hide_url_locale()) {
            return route('front.account.'.$name, $parameters, $absolute);
        }

        return route(front_locale_code().'.front.account.'.$name, $parameters, $absolute);
    }
}

if (! function_exists('hide_url_locale')) {
    /**
     * @return bool
     * @throws Exception
     */
    function hide_url_locale(): bool
    {
        return count(locales()) == 1 && system_setting('hide_url_locale');
    }
}

if (! function_exists('cache_key')) {
    /**
     * @param  $name
     * @param  array  $params
     * @return string
     */
    function cache_key($name, array $params = []): string
    {
        $params['customer_id'] = current_customer_id();
        $params['locale_code'] = front_locale_code();

        return $name.'-'.md5(json_encode($params));
    }
}

if (! function_exists('equal_route_name')) {
    /**
     * Check route is current
     *
     * @param  $routeName
     * @return bool
     */
    function equal_route_name($routeName): bool
    {
        $currentRouteName = Route::getCurrentRoute()->getName();
        $currentRouteName = str_replace(front_locale_code().'.', '', $currentRouteName);
        if (is_string($routeName)) {
            return $currentRouteName == $routeName;
        } elseif (is_array($routeName)) {
            return in_array($currentRouteName, $routeName);
        }

        return false;
    }
}

if (! function_exists('equal_route_param')) {
    /**
     * Check route is current
     *
     * @param  $routeName
     * @param  array  $parameters
     * @return bool
     */
    function equal_route_param($routeName, array $parameters = []): bool
    {
        $currentRouteName = Route::getCurrentRoute()->getName();
        if ($routeName != $currentRouteName) {
            return false;
        }

        $currentRouteParameters = Route::getCurrentRoute()->parameters();

        return $parameters == $currentRouteParameters;
    }
}

if (! function_exists('equal_url')) {
    /**
     * Check url equal current.
     *
     * @param  $url
     * @return bool
     */
    function equal_url($url): bool
    {
        return url()->current() == $url;
    }
}

if (! function_exists('has_debugbar')) {
    /**
     * Check debugbar installed or not
     *
     * @return bool
     */
    function has_debugbar(): bool
    {
        return class_exists(Debugbar::class);
    }
}

if (! function_exists('currencies')) {
    /**
     * @return mixed
     */
    function currencies(): mixed
    {
        return CurrencyRepo::getInstance()->enabledList();
    }
}

if (! function_exists('current_currency')) {
    /**
     * @return mixed
     */
    function current_currency(): mixed
    {
        $currency = currencies()->where('code', current_currency_code())->first();
        if ($currency) {
            return $currency;
        }

        return currencies()->first();
    }
}

if (! function_exists('current_currency_code')) {
    /**
     * @return string
     */
    function current_currency_code(): string
    {
        return Session::get('currency') ?? system_setting('currency', 'usd');
    }
}

if (! function_exists('setting_currency_code')) {
    /**
     * Get setting locale code.
     *
     * @return string
     */
    function setting_currency_code(): string
    {
        return system_setting('currency', 'usd');
    }
}

if (! function_exists('currency_format')) {
    /**
     * @param  $price
     * @param  string  $currency
     * @param  float  $rate
     * @param  bool  $format
     * @return string
     */
    function currency_format($price, string $currency = '', float $rate = 0, bool $format = true): string
    {
        if (! $currency) {
            $currency = is_admin() ? system_setting('currency') : current_currency_code();
        }

        return Currency::getInstance()->format($price, $currency, $rate, $format);
    }
}

if (! function_exists('default_currency')) {
    /**
     * @return mixed
     */
    function default_currency(): mixed
    {
        return currencies()->where('code', system_setting('currency'))->first();
    }
}

if (! function_exists('theme_path')) {
    /**
     * Generate an asset path for the application.
     *
     * @param  string  $path
     * @return string
     */
    function theme_path(string $path): string
    {
        return base_path('themes/'.$path);
    }
}

if (! function_exists('should_copy_static_file')) {
    /**
     * Check if a static file needs to be copied and copy it if necessary
     * Used by theme_asset, theme_image and plugin_asset functions
     *
     * @param  string  $sourceFile  Source file path
     * @param  string  $destFile  Destination file path
     * @return bool True if file exists or was copied successfully
     */
    function should_copy_static_file(string $sourceFile, string $destFile): bool
    {
        $shouldCopy = false;

        // Check if destination file doesn't exist or source file is newer
        if (! file_exists($destFile)) {
            $shouldCopy = true;
        } elseif (file_exists($sourceFile)) {
            $sourceModTime = filemtime($sourceFile);
            $destModTime   = filemtime($destFile);
            if ($sourceModTime > $destModTime) {
                $shouldCopy = true;
            }
        }

        // Copy file if needed
        if ($shouldCopy && file_exists($sourceFile)) {
            create_directories(dirname($destFile));

            return copy($sourceFile, $destFile);
        }

        return file_exists($destFile);
    }
}

if (! function_exists('theme_asset')) {
    /**
     * Generate asset path for the theme, demo code like below:
     * <link rel="stylesheet" href="{{ theme_asset('swiper-bundle.min.css', 'default') }}">
     * swiper-bundle.min.css is in /themes/default/public
     *
     * @param  string  $path  Asset file path
     * @param  string  $theme  Theme name (default taken from system settings)
     * @param  bool|null  $secure  Whether to use HTTPS
     * @return string URL to the asset
     * @throws Exception
     */
    function theme_asset(string $path, string $theme = '', ?bool $secure = null): string
    {
        if (empty($theme)) {
            $theme = system_setting('theme', 'default');
        }
        $originThemePath = "$theme/public/$path";
        $destThemePath   = "static/themes/$theme/$path";

        $sourceFile = theme_path($originThemePath);
        $destFile   = public_path($destThemePath);

        should_copy_static_file($sourceFile, $destFile);

        return app('url')->asset($destThemePath, $secure);
    }
}

if (! function_exists('theme_image')) {
    /**
     * Generate asset path for the theme, demo code like below:
     * <link rel="stylesheet" href="{{ theme_image('preview.jpg', 'default') }}">
     * preview.jpg is in /themes/default/public
     *
     * @param  string  $path  Image file path
     * @param  string  $theme  Theme name (default taken from system settings)
     * @param  int  $width  Desired width for resizing
     * @param  int  $height  Desired height for resizing
     * @return string URL to the resized image
     * @throws Exception
     */
    function theme_image(string $path, string $theme = '', int $width = 100, int $height = 100): string
    {
        if (empty($theme)) {
            $theme = system_setting('theme', 'default');
        }
        $originThemePath = "$theme/public/$path";
        $destThemePath   = "static/themes/$theme/$path";

        $sourceFile = theme_path($originThemePath);
        $destFile   = public_path($destThemePath);

        $fileExists = should_copy_static_file($sourceFile, $destFile);

        if (! $fileExists) {
            return image_resize('', $width, $height);
        }

        return image_resize($destThemePath, $width, $height);
    }
}

if (! function_exists('parse_filters')) {
    /**
     * @param  mixed  $params
     * @return array
     */
    function parse_int_filters(mixed $params): array
    {
        if (is_array($params)) {
            return $params;
        }
        $filters = explode('|', $params);

        return array_filter($filters, function ($filter) {
            return (int) ($filter) > 0;
        });
    }
}

if (! function_exists('parse_attr_filters')) {
    /**
     * @param  mixed  $params
     * @return array|array[]
     * @throws Exception
     */
    function parse_attr_filters(mixed $params): array
    {
        if (is_array($params)) {
            return $params;
        }

        $attributes = explode('|', $params);

        return array_map(function ($item) {
            $itemArr = explode(':', $item);
            if (count($itemArr) != 2) {
                throw new Exception('Invalid attribute parameters!');
            }

            return [
                'attr'  => $itemArr[0],
                'value' => explode(',', $itemArr[1]),
            ];
        }, $attributes);
    }
}

if (! function_exists('innoshop_version')) {
    /**
     * Generate an asset path for the application.
     *
     * @return string
     */
    function innoshop_version(): string
    {
        $default = ucfirst(config('innoshop.edition')).' v'.config('innoshop.version').'('.config('innoshop.build').')';

        return fire_hook_filter('innoshop.version.display', $default);
    }
}

if (! function_exists('innoshop_brand_link')) {
    /**
     * Get innoshop brand link
     *
     * @return string
     */
    function innoshop_brand_link(): string
    {
        if (is_admin()) {
            $default = '<a href="https://www.innoshop.com" class="ms-2" target="_blank">InnoShop</a>';
        } else {
            $default = 'Powered By <a href="https://www.innoshop.com" class="ms-2" target="_blank">InnoShop</a>';
        }

        return fire_hook_filter('innoshop.brand.link.display', $default);
    }
}

if (! function_exists('to_sql')) {
    /**
     * Render SQL by builder object
     * @param  mixed  $builder
     * @return string
     */
    function to_sql(mixed $builder): string
    {
        $sql    = $builder->toSql();
        $driver = DB::getDriverName();
        if ($driver == 'mysql') {
            $sql = str_replace('"', '`', $sql);
        }

        foreach ($builder->getBindings() as $binding) {
            $value = is_numeric($binding) ? $binding : "'".$binding."'";
            $sql   = preg_replace('/\?/', $value, $sql, 1);
        }

        return $sql;
    }
}

if (! function_exists('seller_enabled')) {
    /**
     * Get available locales
     *
     * @return bool
     */
    function seller_enabled(): bool
    {
        return class_exists('\InnoShop\Seller\SellerServiceProvider') && env('SELLER_ENABLED', true);
    }
}

if (! function_exists('parsedown')) {
    /**
     * @param  string|null  $value
     * @param  bool|null  $inline
     * @return Parsedown|string
     */
    function parsedown(?string $value = null, ?bool $inline = null): Parsedown|string
    {
        $parser = new Parsedown;

        if (! func_num_args()) {
            return $parser;
        }

        if (is_null($inline)) {
            $inline = config('parsedown.inline');
        }

        if ($inline) {
            return $parser->line($value);
        }

        return $parser->text($value);
    }
}

if (! function_exists('weight_convert')) {
    /**
     * Convert weight from one unit to another
     *
     * @param  float  $value  Weight value
     * @param  string  $fromCode  Source weight unit code
     * @param  string  $toCode  Target weight unit code
     * @return float
     * @throws Exception
     */
    function weight_convert(float $value, string $fromCode, string $toCode): float
    {
        return Weight::getInstance()->convert($value, $fromCode, $toCode);
    }
}

if (! function_exists('weight_format')) {
    /**
     * Format weight with unit
     *
     * @param  float  $value  Weight value
     * @param  string  $code  Weight unit code
     * @return string
     * @throws Exception
     */
    function weight_format(float $value, string $code): string
    {
        return Weight::getInstance()->format($value, $code);
    }
}

if (! function_exists('weight_to_default')) {
    /**
     * Convert weight to system default unit
     *
     * @param  float  $value  Weight value
     * @param  string  $fromCode  Source weight unit code
     * @return float
     * @throws Exception
     */
    function weight_to_default(float $value, string $fromCode): float
    {
        return Weight::getInstance()->toDefault($value, $fromCode);
    }
}

if (! function_exists('register')) {
    /**
     * Register data to registry
     *
     * @param  array|string  $key
     * @param  mixed  $value
     */
    function register(array|string $key, mixed $value): void
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                Registry::set($k, $v);
            }
        }

        Registry::set($key, $value);
    }
}

if (! function_exists('registry')) {
    /**
     * Get data from registry
     *
     * @param  string  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    function registry(string $key, mixed $default = null): mixed
    {
        return Registry::get($key, $default);
    }
}
