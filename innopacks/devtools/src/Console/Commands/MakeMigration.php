<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use InnoShop\DevTools\Services\ScaffoldService;

class MakeMigration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:make-migration {name : The name of the migration (e.g., create_users_table)} {--table= : The table name} {--plugin= : Plugin path (if not in plugins directory)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new migration file for a plugin';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name      = $this->argument('name');
        $tableName = $this->option('table');

        // Extract table name from migration name if not provided
        if (! $tableName) {
            if (preg_match('/create_(.+?)_table/', $name, $matches)) {
                $tableName = $matches[1];
            } else {
                $this->error('Table name is required. Use --table=table_name or name migration as create_table_name_table');

                return Command::FAILURE;
            }
        }

        // Determine plugin path
        $pluginPath = $this->option('plugin');
        if (! $pluginPath) {
            // Try to detect from current directory
            $currentDir = getcwd();
            if (strpos($currentDir, 'plugins') !== false) {
                $pluginPath = $currentDir;
            } else {
                $this->error('Plugin path is required. Use --plugin=path/to/plugin or run from plugin directory');

                return Command::FAILURE;
            }
        } else {
            if (! $this->isAbsolutePath($pluginPath)) {
                $pluginPath = base_path($pluginPath);
            }
        }

        if (! File::exists($pluginPath)) {
            $this->error("Plugin path does not exist: {$pluginPath}");

            return Command::FAILURE;
        }

        // Extract plugin name from path
        $pluginName = basename($pluginPath);

        try {
            $service = new ScaffoldService;
            $service->generateMigrationForPlugin($pluginPath, $pluginName, $name, $tableName);

            $this->info('Migration created successfully!');
            $migrationPath = "{$pluginPath}/Database/Migrations";
            $this->line("Migration path: {$migrationPath}");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create migration: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }

    /**
     * Check if path is absolute.
     *
     * @param  string  $path
     * @return bool
     */
    private function isAbsolutePath(string $path): bool
    {
        return strpos($path, '/') === 0 || (PHP_OS_FAMILY === 'Windows' && preg_match('/^[A-Z]:\\\\/', $path));
    }
}
