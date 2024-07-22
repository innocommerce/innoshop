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
        return new static;
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
    }

    /**
     * Migrate plugin database.
     *
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function migrateDatabase(CPlugin $CPlugin): void
    {
        $migrationPath = "{$CPlugin->getPath()}/Migrations";
        if (is_dir($migrationPath)) {
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
    }

    /**
     * @param  CPlugin  $CPlugin
     * @return void
     */
    public function rollbackDatabase(CPlugin $CPlugin): void
    {
        $migrationPath = "{$CPlugin->getPath()}/Migrations";
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
}
