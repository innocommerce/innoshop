<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use Barryvdh\Debugbar\Facades\Debugbar;
use Illuminate\Support\Facades\Log;
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
     * Gets asset from a plugin.
     * Format: plugin_asset('Stripe', 'css/swiper-bundle.min.css')
     * so, swiper-bundle.min.css is in /plugins/Stripe/Public/css/
     *
     * @param  string  $pluginCode  Plugin code/name
     * @param  string  $path  Asset file path within plugin
     * @param  bool|null  $secure  Whether to use HTTPS
     * @return string URL to the asset
     */
    function plugin_asset(string $pluginCode, string $path, ?bool $secure = null): string
    {
        $pluginDirectory  = Str::studly($pluginCode);
        $originPluginPath = "$pluginDirectory/Public/$path";
        $destPluginPath   = strtolower("static/plugins/$pluginCode/$path");

        $sourceFile = plugin_path($originPluginPath);
        $destFile   = public_path($destPluginPath);

        should_copy_static_file($sourceFile, $destFile);

        return app('url')->asset($destPluginPath, $secure);
    }
}

if (! function_exists('plugin_locale_code')) {
    /**
     * Get plugin locale code
     *
     * @return string
     * @throws Exception
     */
    function plugin_locale_code(): string
    {
        if (is_admin()) {
            return panel_locale_code();
        }

        return front_locale_code();
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

if (! function_exists('add_model_relation')) {
    /**
     * Add a dynamic relation to a model.
     *
     * @param  string  $modelClass  The model class name (e.g., Product::class)
     * @param  string  $relationName  The name of the relation (e.g., 'seller')
     * @param  \Closure  $callback  The closure that defines the relation
     * @param  array  $options  Additional options:
     *                          - 'override' (bool): Allow overriding existing relation (default: false)
     * @return bool Returns true if relation was added successfully, false otherwise
     * @throws \InvalidArgumentException
     */
    function add_model_relation(string $modelClass, string $relationName, \Closure $callback, array $options = []): bool
    {
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException("Model class {$modelClass} does not exist");
        }

        if (! is_subclass_of($modelClass, \Illuminate\Database\Eloquent\Model::class)) {
            throw new \InvalidArgumentException("{$modelClass} must be an Eloquent Model");
        }

        $allowOverride = $options['override'] ?? false;
        if (method_exists($modelClass, $relationName) && ! $allowOverride) {
            if (config('app.debug')) {
                Log::warning("Relation {$relationName} already exists on {$modelClass}. Use 'override' => true to override.");
            }

            return false;
        }

        if (method_exists($modelClass, 'resolveRelationUsing')) {
            $modelClass::resolveRelationUsing($relationName, $callback);

            return true;
        }

        return false;
    }
}

if (! function_exists('add_model_relations')) {
    /**
     * Add multiple relations to a model at once.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $relations  Array of relations: ['relationName' => Closure, ...]
     * @param  array  $options  Options passed to add_model_relation
     * @return array Returns array of results: ['relationName' => bool, ...]
     */
    function add_model_relations(string $modelClass, array $relations, array $options = []): array
    {
        $results = [];

        foreach ($relations as $relationName => $callback) {
            if (! ($callback instanceof \Closure)) {
                if (config('app.debug')) {
                    Log::warning("Invalid callback for relation {$relationName} on {$modelClass}");
                }
                $results[$relationName] = false;

                continue;
            }

            $results[$relationName] = add_model_relation($modelClass, $relationName, $callback, $options);
        }

        return $results;
    }
}
