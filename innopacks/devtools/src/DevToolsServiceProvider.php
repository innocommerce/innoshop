<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools;

use Illuminate\Support\ServiceProvider;

class DevToolsServiceProvider extends ServiceProvider
{
    /**
     * config path.
     */
    private string $basePath = __DIR__.'/../';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->basePath.'config/devtools.php', 'devtools');
    }

    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $this->registerCommands();
    }

    /**
     * Register commands.
     *
     * @return void
     */
    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $commandClasses = [
                Console\Commands\MakePlugin::class,
                Console\Commands\MakeTheme::class,
                Console\Commands\MakeController::class,
                Console\Commands\MakeModel::class,
                Console\Commands\MakeService::class,
                Console\Commands\MakeRepository::class,
                Console\Commands\MakeMigration::class,
                Console\Commands\ValidatePlugin::class,
                Console\Commands\ValidateTheme::class,
                Console\Commands\PublishPlugin::class,
                Console\Commands\PublishTheme::class,
                Console\Commands\InitPluginGit::class,
                Console\Commands\SetGiteaToken::class,
            ];

            // Register commands with dev: prefix
            $this->commands($commandClasses);

            // Register aliases with devtools: prefix
            // Create alias commands by cloning and renaming
            $aliasedCommands = [];
            foreach ($commandClasses as $commandClass) {
                $command      = $this->app->make($commandClass);
                $originalName = $command->getName();
                if (strpos($originalName, 'dev:') === 0) {
                    $aliasName = str_replace('dev:', 'devtools:', $originalName);
                    // Create a new instance with the alias name
                    $reflection   = new \ReflectionClass($commandClass);
                    $aliasCommand = $reflection->newInstance();
                    $aliasCommand->setName($aliasName);
                    $aliasedCommands[] = $aliasCommand;
                }
            }
            $this->commands($aliasedCommands);
        }
    }
}
