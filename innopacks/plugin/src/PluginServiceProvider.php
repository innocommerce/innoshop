<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin;

use Exception;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use InnoShop\Panel\Middleware\SetPanelLocale;
use InnoShop\Plugin\Core\PluginManager;

class PluginServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Plugin base path.
     * @var string
     */
    private string $pluginBasePath = '';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('plugin', function () {
            return new PluginManager;
        });
    }

    /**
     * Boot Plugin Service Provider
     *
     * @throws Exception
     */
    public function boot(): void
    {
        $this->registerMigrations();

        if (! installed()) {
            return;
        }

        $this->registerBladeInsertDirectives();
        $this->registerBladeUpdateDirectives();

        if (! Schema::hasTable('plugins')) {
            return;
        }

        $this->registerWebRoutes();
        $this->loadViewTemplates();

        $this->pluginBasePath = base_path('plugins');
        $this->bootAllPlugins();
        $this->bootEnabledPlugins();
    }

    /**
     * Register admin panel routes.
     *
     * @return void
     */
    private function registerWebRoutes(): void
    {
        $adminName = panel_name();
        Route::prefix($adminName)
            ->middleware(['web', 'admin_auth:admin', SetPanelLocale::class])
            ->name("$adminName.")
            ->group(function () {
                $this->loadRoutesFrom(realpath(__DIR__.'/../routes/web.php'));
            });
    }

    /**
     * Load templates
     *
     * @return void
     */
    private function loadViewTemplates(): void
    {
        $this->loadViewsFrom(inno_path('plugin/resources/views'), 'plugin');
    }

    /**
     * Register migrations.
     *
     * @return void
     */
    private function registerMigrations(): void
    {
        $this->loadMigrationsFrom($this->basePath.'database/migrations');
    }

    /**
     * Load all plugins and boot them.
     *
     * @return void
     */
    protected function bootAllPlugins(): void
    {
        $allPlugins = app('plugin')->getPlugins();
        $commands   = [];
        foreach ($allPlugins as $plugin) {
            $pluginCode = $plugin->getDirname();
            $this->loadPluginMigrations($pluginCode);
            $this->loadPluginViews($pluginCode);
            $this->loadPluginTranslations($pluginCode);
            $commands = array_merge($commands, $this->loadPluginCommands($pluginCode));
        }
        $this->runPluginCommands($commands);
    }

    /**
     * Load enabled plugins and boot them.
     *
     * @return void
     * @throws Exception
     */
    protected function bootEnabledPlugins(): void
    {
        $enabledPlugins = app('plugin')->getEnabledPlugins();
        foreach ($enabledPlugins as $plugin) {
            $pluginCode = $plugin->getDirname();
            $this->bootPlugin($plugin);
            $this->loadPluginRoutes($pluginCode);
            $this->loadPluginMiddlewares($pluginCode);
        }
    }

    /**
     * Call Plugin Boot::init()
     *
     * @param  $plugin
     */
    private function bootPlugin($plugin): void
    {
        $filePath   = $plugin->getBootFile();
        $pluginCode = $plugin->getDirname();
        if (file_exists($filePath)) {
            $className = "Plugin\\$pluginCode\\Boot";
            if (method_exists($className, 'init')) {
                (new $className)->init();
            }
        }
    }

    /**
     * Register common commands.
     *
     * @param  $pluginCode
     * @return array
     */
    private function loadPluginCommands($pluginCode): array
    {
        $commandsPath = "$this->pluginBasePath/$pluginCode/Commands";
        if (! is_dir($commandsPath)) {
            return [];
        }

        return $this->getClassesFromPath($commandsPath);
    }

    /**
     * @param  $commands
     * @return void
     */
    private function runPluginCommands($commands): void
    {
        if (empty($commands)) {
            return;
        }

        if ($this->app->runningInConsole()) {
            $this->commands($commands);
        }
    }

    /**
     * Load plugin migrations
     *
     * @param  $pluginCode
     */
    private function loadPluginMigrations($pluginCode): void
    {
        $migrationPath = "$this->pluginBasePath/$pluginCode/Migrations";
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Load and register routes for panel and shop
     *
     * @param  $pluginCode
     * @throws Exception
     */
    private function loadPluginRoutes($pluginCode): void
    {
        $this->loadPluginPanelRoutes($pluginCode);
        $this->loadPluginFrontRoutes($pluginCode);
        $this->loadPluginFrontApiRoutes($pluginCode);
    }

    /**
     * Register panel routes
     *
     * @param  $pluginCode
     */
    private function loadPluginPanelRoutes($pluginCode): void
    {
        $pluginBasePath = $this->pluginBasePath;
        $adminRoutePath = "$pluginBasePath/$pluginCode/Routes/panel.php";
        if (file_exists($adminRoutePath)) {
            $adminName = panel_name();
            Route::prefix($adminName)
                ->name("$adminName.")
                ->middleware(['panel', 'admin_auth:admin'])
                ->group(function () use ($adminRoutePath) {
                    $this->loadRoutesFrom($adminRoutePath);
                });
        }
    }

    /**
     * Register frontend shop routes
     *
     * @param  $pluginCode
     * @throws Exception
     */
    private function loadPluginFrontRoutes($pluginCode): void
    {
        $pluginBasePath = $this->pluginBasePath;
        $frontRoutePath = "$pluginBasePath/$pluginCode/Routes/front.php";
        if (file_exists($frontRoutePath)) {
            $locales = locales();
            if (count($locales) == 1) {
                Route::middleware('front')
                    ->name('front.')
                    ->group(function () use ($frontRoutePath) {
                        $this->loadRoutesFrom($frontRoutePath);
                    });
            } else {
                foreach ($locales as $locale) {
                    Route::middleware('front')
                        ->prefix($locale->code)
                        ->name($locale->code.'.front.')
                        ->group(function () use ($frontRoutePath) {
                            $this->loadRoutesFrom($frontRoutePath);
                        });
                }
            }
        }
    }

    /**
     * Register frontend api routes.
     *
     * @param  $pluginCode
     * @return void
     */
    protected function loadPluginFrontApiRoutes($pluginCode): void
    {
        $pluginBasePath    = $this->pluginBasePath;
        $frontApiRoutePath = "$pluginBasePath/$pluginCode/Routes/api.php";
        if (file_exists($frontApiRoutePath)) {
            Route::prefix('api')
                ->middleware('api')
                ->name('api.')
                ->group(function () use ($frontApiRoutePath) {
                    $this->loadRoutesFrom($frontApiRoutePath);
                });
        }
    }

    /**
     * Load plugin languages.
     *
     * @param  $pluginCode
     * @return void
     */
    private function loadPluginTranslations($pluginCode): void
    {
        $pluginBasePath = $this->pluginBasePath;
        $this->loadTranslationsFrom("$pluginBasePath/$pluginCode/Lang", $pluginCode);
    }

    /**
     * Load plugin view path
     *
     * @param  $pluginCode
     */
    private function loadPluginViews($pluginCode): void
    {
        $pluginViewPath = $this->pluginBasePath."/$pluginCode/Views";
        if (file_exists($pluginViewPath)) {
            $this->loadViewsFrom($pluginViewPath, $pluginCode);
        }
    }

    /**
     * Register plugin middlewares
     */
    private function loadPluginMiddlewares($pluginCode): void
    {
        $pluginBasePath = $this->pluginBasePath;
        $middlewarePath = "$pluginBasePath/$pluginCode/Middleware";

        $router           = $this->app['router'];
        $frontMiddlewares = $this->getClassesFromPath("$middlewarePath/Front");
        $panelMiddlewares = $this->getClassesFromPath("$middlewarePath/Panel");

        if ($frontMiddlewares) {
            foreach ($frontMiddlewares as $shopMiddleware) {
                $router->pushMiddlewareToGroup('front', $shopMiddleware);
            }
        }

        if ($panelMiddlewares) {
            foreach ($panelMiddlewares as $adminMiddleware) {
                $router->pushMiddlewareToGroup('panel', $adminMiddleware);
            }
        }
    }

    /**
     * Get middlewares from plugin path.
     *
     * @param  $path
     * @return array
     */
    private function getClassesFromPath($path): array
    {
        if (! file_exists($path)) {
            return [];
        }

        $middlewares = [];
        $files       = glob("$path/*");
        foreach ($files as $file) {
            $baseName      = basename($file, '.php');
            $namespacePath = 'Plugin'.dirname(str_replace($this->pluginBasePath, '', $file)).'/';
            $className     = str_replace('/', '\\', $namespacePath.$baseName);

            if (class_exists($className)) {
                $middlewares[] = $className;
            }
        }

        return $middlewares;
    }

    /**
     * Add a Blade hook tag without needing @endhook.
     * Use @hookinsert('example_hook_name') to directly output the hook content to the page.
     */
    private function registerBladeInsertDirectives(): void
    {
        Blade::directive('hookinsert', function ($parameter) {
            $parameter  = trim($parameter, '()');
            $parameters = explode(',', $parameter);

            $name        = trim($parameters[0], "'");
            $definedVars = $this->parseParameters($parameters);

            return ' <?php
                $__definedVars = (get_defined_vars()["__data"]);
                if (empty($__definedVars))
                {
                    $__definedVars = [];
                }
                '.$definedVars.'
                $output = \InnoShop\Plugin\Core\Blade\Hook::getSingleton()->getHook("'.$name.'",["data"=>$__definedVars],function($data) { return null; });
                if ($output)
                echo $output;
                ?>';
        });
    }

    /**
     * Add a Blade wrapper hook tag
     * Use @hookupdate('example_hook_name') to @endhookupdate, package a section of code and output it using a hook.
     */
    private function registerBladeUpdateDirectives(): void
    {
        Blade::directive('hookupdate', function ($parameter) {
            $parameter  = trim($parameter, '()');
            $parameters = explode(',', $parameter);
            $name       = trim($parameters[0], "'");

            return ' <?php
                    $__hook_name="'.$name.'";
                    ob_start();
                ?>';
        });

        Blade::directive('endhookupdate', function () {
            return ' <?php
                $__definedVars = (get_defined_vars()["__data"]);
                if (empty($__definedVars))
                {
                    $__definedVars = [];
                }
                $__hook_content = ob_get_clean();
                $output = \InnoShop\Plugin\Core\Blade\Hook::getSingleton()->getWrapper("$__hook_name",["data"=>$__definedVars],function($data) { return null; },$__hook_content);
                unset($__hook_name);
                unset($__hook_content);
                if ($output)
                echo $output;
                ?>';
        });
    }

    /**
     * Parse parameters from Blade
     *
     * @param  $parameters
     * @return string
     */
    protected function parseParameters($parameters): string
    {
        $definedVars = '';
        foreach ($parameters as $paraItem) {
            $paraItem = trim($paraItem);
            if (Str::startsWith($paraItem, '$')) {
                $paraKey = trim($paraItem, '$');
                $definedVars .= '$__definedVars["'.$paraKey.'"] = $'.$paraKey.';';
            }
        }

        return $definedVars;
    }
}
