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
use Illuminate\Support\Str;
use InnoShop\DevTools\Services\ScaffoldService;

class MakeModel extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:make-model {name : The name of the model (PluginName/ModelName)} {--plugin= : Plugin path (if not in plugins directory)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new model for a plugin';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name  = $this->argument('name');
        $parts = explode('/', $name);

        if (count($parts) < 2) {
            $this->error('Model name must be in format: PluginName/ModelName');

            return Command::FAILURE;
        }

        $pluginName = $parts[0];
        $modelName  = $parts[1];

        // Determine plugin path
        $pluginPath = $this->option('plugin');
        if (! $pluginPath) {
            $pluginPath = base_path("plugins/{$pluginName}");
        } else {
            if (! $this->isAbsolutePath($pluginPath)) {
                $pluginPath = base_path($pluginPath);
            }
        }

        if (! File::exists($pluginPath)) {
            $this->error("Plugin path does not exist: {$pluginPath}");

            return Command::FAILURE;
        }

        try {
            $service = new ScaffoldService;
            $service->generateModelForPlugin($pluginPath, $pluginName, $modelName);

            $this->info('Model created successfully!');
            $this->line("Model path: {$pluginPath}/Models/".Str::studly($modelName).'.php');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create model: {$e->getMessage()}");

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
