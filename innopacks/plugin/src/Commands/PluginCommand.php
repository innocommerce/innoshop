<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Commands;

use Illuminate\Console\Command;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Plugin\Core\Plugin;
use InnoShop\Plugin\PluginManager;
use InnoShop\Plugin\Repositories\PluginRepo;
use InnoShop\Plugin\Services\PluginService;
use Throwable;

abstract class PluginCommand extends Command
{
    /**
     * Resolve a plugin by code, fail loudly if missing.
     *
     * @param  string  $code  snake_case plugin code (e.g. mobile_builder)
     */
    protected function resolvePlugin(string $code): Plugin
    {
        $plugin = app('plugin')->getPlugin($code);
        if (! $plugin) {
            $this->error("Plugin not found: {$code}");
            $this->line('Run <info>php artisan plugin:list</info> to see available codes.');

            exit(1);
        }

        return $plugin;
    }

    /**
     * Persist install state. Runs migrations + INSERT row + cache reset.
     */
    protected function install(Plugin $plugin): void
    {
        PluginService::getInstance()->installPlugin($plugin);
        PluginRepo::resetCache();
    }

    /**
     * Rollback install. Reverse migrations + DELETE row.
     */
    protected function uninstall(Plugin $plugin): void
    {
        PluginService::getInstance()->uninstallPlugin($plugin);
        PluginRepo::resetCache();
    }

    /**
     * Flip the settings.<code>.active flag.
     */
    protected function setActive(Plugin $plugin, bool $active): void
    {
        SettingRepo::getInstance()->updatePluginValue($plugin->getCode(), 'active', $active);
        $this->callSilent('view:clear');
    }

    /**
     * Format a one-line state badge for tables.
     */
    protected function stateLabel(Plugin $plugin): string
    {
        if (! $plugin->checkInstalled()) {
            return '<fg=gray>available</>';
        }

        return $plugin->checkActive() ? '<fg=green>enabled</>' : '<fg=yellow>disabled</>';
    }

    /**
     * Print a summary table of plugins.
     *
     * @param  Plugin[]|PluginManager  $plugins
     */
    protected function tablePlugins($plugins): void
    {
        $rows = [];
        foreach ($plugins as $plugin) {
            $rows[] = [
                $plugin->getCode(),
                $plugin->getDirname(),
                $plugin->getType(),
                $plugin->getVersion(),
                $plugin->checkInstalled() ? 'Y' : '<fg=gray>-</>',
                $plugin->checkActive() ? '<fg=green>Y</>' : '<fg=gray>-</>',
                $plugin->getLocaleName(),
            ];
        }

        $this->table(
            ['code', 'dir', 'type', 'version', 'inst', 'active', 'name'],
            $rows
        );
    }

    /**
     * Read plugin seeder files (if any) and run them in order.
     */
    protected function runSeeders(Plugin $plugin): array
    {
        $dir = $plugin->getPath().'/Database/Seeders';
        if (! is_dir($dir)) {
            return ['ran' => 0, 'total' => 0, 'errors' => []];
        }

        $files = glob($dir.'/*.php');
        sort($files);

        $ran    = 0;
        $errors = [];
        foreach ($files as $file) {
            $class = 'Plugin\\'.$plugin->getDirname().'\\Database\\Seeders\\'.basename($file, '.php');
            if (! class_exists($class)) {
                require_once $file;
            }
            if (! class_exists($class)) {
                $errors[] = "{$class}: class not found after require";

                continue;
            }
            try {
                (new $class)->run();
                $ran++;
            } catch (Throwable $e) {
                $errors[] = "{$class}: ".$e->getMessage();
            }
        }

        return ['ran' => $ran, 'total' => count($files), 'errors' => $errors];
    }
}
