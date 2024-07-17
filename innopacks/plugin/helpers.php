<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Str;
use InnoShop\Common\Services\ImageService;
use InnoShop\Plugin\Core\Blade\Hook;
use InnoShop\Plugin\Core\Plugin;

if (! function_exists('plugin')) {
    /**
     * Get plugin object.
     *
     * @param  $code
     * @return Plugin|null
     */
    function plugin($code): ?Plugin
    {
        return app('plugin')->getPlugin($code);
    }
}

if (! function_exists('plugin_setting')) {
    /**
     * @param  $code
     * @param  string  $key
     * @param  null  $default
     * @return mixed
     */
    function plugin_setting($code, string $key = '', $default = null): mixed
    {
        $code = Str::snake($code);
        if ($key) {
            return setting("$code.$key", $default);
        }

        return setting("$code", $default);
    }
}

if (! function_exists('plugin_path')) {
    /**
     * Get plugin path
     *
     * @param  string  $path
     * @return string
     */
    function plugin_path(string $path = ''): string
    {
        return base_path('plugins').($path ? '/'.ltrim($path, '/') : $path);
    }
}

if (! function_exists('plugin_resize')) {
    /**
     * Resize plugin image
     *
     * @param  $pluginCode
     * @param  $image
     * @param  int  $width
     * @param  int  $height
     * @return mixed|void
     * @throws Exception
     */
    function plugin_resize($pluginCode, $image, int $width = 100, int $height = 100)
    {
        if (Str::startsWith($image, 'http')) {
            return $image;
        }

        $plugin        = plugin($pluginCode);
        $pluginDirName = $plugin->getDirname();

        return ImageService::getInstance($image)->setPluginDirName($pluginDirName)->resize($width, $height);
    }
}

if (! function_exists('plugin_origin')) {
    /**
     * Get origin image from plugin
     *
     * @param  $pluginCode
     * @param  $image
     * @return mixed|void
     * @throws Exception
     */
    function plugin_origin($pluginCode, $image)
    {
        if (Str::startsWith($image, 'http')) {
            return $image;
        }

        $plugin        = plugin($pluginCode);
        $pluginDirName = $plugin->getDirname();

        return ImageService::getInstance($image)->setPluginDirName($pluginDirName)->originUrl();
    }
}

if (! function_exists('plugin_asset')) {
    /**
     * Generate asset path for the plugin, demo code like below:
     * <link rel="stylesheet" href="{{ plugin_asset('stripe','swiper-bundle.min.css') }}">,
     * swiper-bundle.min.css is in /plugins/Stripe/Public
     *
     * @param  string  $pluginCode
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function plugin_asset(string $pluginCode, string $path, ?bool $secure = null): string
    {
        $pluginDirectory  = Str::studly($pluginCode);
        $originPluginPath = "$pluginDirectory/Public/$path";
        $destPluginPath   = strtolower("plugins/$pluginCode/$path");
        if (! file_exists(public_path($destPluginPath))) {
            create_directories(dirname(public_path($destPluginPath)));
            copy(plugin_path($originPluginPath), public_path($destPluginPath));
        }

        return app('url')->asset($destPluginPath, $secure);
    }
}

if (! function_exists('fire_hook_action')) {
    /**
     * Fire(Trigger) hook action, used in system.
     *
     * @param  $hookName
     * @param  null  $data
     * @return void
     */
    function fire_hook_action($hookName, $data = null): void
    {
        if (config('app.debug') && has_debugbar()) {
            Debugbar::log('HOOK === fire_hook_action: '.$hookName);
        }
        app('eventy')->action($hookName, $data);
    }
}

if (! function_exists('listen_hook_action')) {
    /**
     * Listen(Bind) hook action, used in plugin.
     *
     * @param  $hookName
     * @param  $callback
     * @param  int  $priority
     * @param  int  $arguments
     * @return void
     */
    function listen_hook_action($hookName, $callback, int $priority = 20, int $arguments = 1): void
    {
        if ($priority === 0) {
            $priority = plugin_caller_priority();
        }
        app('eventy')->addAction($hookName, $callback, $priority, $arguments);
    }
}

if (! function_exists('fire_hook_filter')) {
    /**
     * Fire(Trigger) hook filter, used in system.
     *
     * @param  $hookName
     * @param  mixed  $data
     * @return mixed
     */
    function fire_hook_filter($hookName, mixed $data): mixed
    {
        if (config('app.debug') && has_debugbar()) {
            Debugbar::log('HOOK === fire_hook_filter: '.$hookName);
        }

        return app('eventy')->filter($hookName, $data);
    }
}

if (! function_exists('listen_hook_filter')) {
    /**
     * Listen(Bind) hook filter, used in plugin.
     *
     * @param  $hookName
     * @param  $callback
     * @param  int  $priority
     * @param  int  $arguments
     * @return void
     */
    function listen_hook_filter($hookName, $callback, int $priority = 0, int $arguments = 1): void
    {
        if ($priority === 0) {
            $priority = plugin_caller_priority();
        }
        app('eventy')->addFilter($hookName, $callback, $priority, $arguments);
    }
}

if (! function_exists('listen_blade_insert')) {
    /**
     * Listen(bind) a hook to modify Blade code.
     * The callback method $callback only requires one argument: $data.
     *
     * @param  $hookName
     * @param  $callback
     * @param  int  $priority
     * @return void
     */
    function listen_blade_insert($hookName, $callback, int $priority = 0): void
    {
        if ($priority === 0) {
            $priority = plugin_caller_priority();
        }
        Hook::getSingleton()->listen($hookName, $callback, $priority);
    }
}

if (! function_exists('listen_blade_update')) {
    /**
     * Listen(bind) a hook to modify Blade code.
     * The callback method $callback requires two argument: $output && $data.
     *
     * @param  $hookName
     * @param  $callback
     * @param  int  $priority
     * @return void
     */
    function listen_blade_update($hookName, $callback, int $priority = 0): void
    {
        if ($priority === 0) {
            $priority = plugin_caller_priority();
        }
        Hook::getSingleton()->listen($hookName, $callback, $priority);
    }
}

if (! function_exists('plugin_caller_priority')) {
    /**
     * Retrieve the priority of the caller plugin.
     *
     * @return int
     */
    function plugin_caller_priority(): int
    {
        $trace      = debug_backtrace();
        $pluginCode = str_replace(['Plugin\\', '\Boot'], '', $trace[2]['class']);
        $plugin     = app('plugin')->getPlugin($pluginCode);

        return (int) ($plugin ? $plugin->getPriority() : 0);
    }
}
