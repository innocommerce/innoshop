<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Services;

use Exception;
use Illuminate\Support\Facades\Artisan;
use InnoShop\Plugin\Core\Plugin as CPlugin;
use InnoShop\Plugin\Models\Plugin;
use InnoShop\Plugin\Repositories\PluginRepo;

class PluginService
{
    private PluginRepo $pluginRepo;

    public function __construct()
    {
        $this->pluginRepo = PluginRepo::getInstance();
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new self;
    }

    /**
     * Install plugin.
     *
     * @param  CPlugin  $CPlugin
     * @throws Exception
     */
    public function installPlugin(CPlugin $CPlugin): void
    {
        $this->migrateDatabase($CPlugin);
        $type = $CPlugin->getType();
        $code = $CPlugin->getCode();

        $params = [
            'type'     => $type,
            'code'     => $code,
            'priority' => 0,
        ];
        $plugin = $this->pluginRepo->getBuilder($params)->first();
        if (empty($plugin)) {
            Plugin::query()->create($params);
        }
        PluginRepo::resetCache();
    }

    /**
     * Migrate plugin database.
     *
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function migrateDatabase(CPlugin $CPlugin): void
    {
        $migrationPath = "{$CPlugin->getPath()}/Database/Migrations";
        if (! is_dir($migrationPath)) {
            return;
        }
        $files = glob($migrationPath.'/*');
        asort($files);

        foreach ($files as $file) {
            $file = str_replace(base_path(), '', $file);
            Artisan::call('migrate', [
                '--force' => true,
                '--step'  => 1,
                '--path'  => $file,
            ]);
        }
    }

    /**
     * Uninstall plugin
     *
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function uninstallPlugin(CPlugin $CPlugin): void
    {
        $this->rollbackDatabase($CPlugin);
        $filters = [
            'type' => $CPlugin->getType(),
            'code' => $CPlugin->getCode(),
        ];
        $this->pluginRepo->getBuilder($filters)->delete();
        PluginRepo::resetCache();
    }

    /**
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function rollbackDatabase(CPlugin $CPlugin): void
    {
        $migrationPath = "{$CPlugin->getPath()}/Database/Migrations";
        if (! is_dir($migrationPath)) {
            return;
        }

        $files = glob($migrationPath.'/*');
        arsort($files);
        foreach ($files as $file) {
            $file = str_replace(base_path(), '', $file);
            Artisan::call('migrate:rollback', [
                '--force' => true,
                '--step'  => 1,
                '--path'  => $file,
            ]);
        }
    }

    /**
     * Reset plugin database: rollback → migrate → seed.
     *
     * @param  CPlugin  $CPlugin
     * @param  bool  $clearData
     * @return void
     */
    public function resetPlugin(CPlugin $CPlugin, bool $clearData = false): void
    {
        $this->rollbackDatabase($CPlugin);
        $this->migrateDatabase($CPlugin);
        $this->runSeeders($CPlugin, $clearData);
    }

    /**
     * Run plugin seeders manually.
     *
     * @param  CPlugin  $CPlugin
     * @param  bool  $clearData
     * @return void
     */
    public function runSeeders(CPlugin $CPlugin, bool $clearData = false): void
    {
        $seederPath = "{$CPlugin->getPath()}/Database/Seeders";
        if (! is_dir($seederPath)) {
            return;
        }

        $pluginCode = $CPlugin->getDirname();
        $files      = glob("$seederPath/*.php");
        sort($files);

        foreach ($files as $file) {
            $className = basename($file, '.php');
            $fullClass = "Plugin\\{$pluginCode}\\Database\\Seeders\\{$className}";
            if (class_exists($fullClass) && method_exists($fullClass, 'run')) {
                $ref  = new \ReflectionMethod($fullClass, 'run');
                $args = $ref->getNumberOfParameters();
                if ($args > 0) {
                    (new $fullClass)->run($clearData);
                } else {
                    (new $fullClass)->run();
                }
            }
        }
    }
}
