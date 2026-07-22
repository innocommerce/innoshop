<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Commands;

use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'plugin:install', description: 'Install a plugin (run migrations + register row)')]
class PluginInstallCommand extends PluginCommand
{
    protected $signature = 'plugin:install
        {code : Plugin code (snake_case, e.g. mobile_builder)}
        {--enable : Also enable the plugin after install}';

    protected $description = 'Install a plugin (run migrations + register row)';

    public function handle(): int
    {
        $code   = (string) $this->argument('code');
        $plugin = $this->resolvePlugin($code);

        $wasInstalled = $plugin->checkInstalled();
        if ($wasInstalled) {
            $this->warn("Already installed: {$code}");
        } else {
            $this->install($plugin);
            $this->info("Installed: {$code}");
        }

        if ($this->option('enable') && ! $plugin->checkActive()) {
            $this->setActive($plugin, true);
            $this->info("→ enabled: {$code}");
        }

        return self::SUCCESS;
    }
}
