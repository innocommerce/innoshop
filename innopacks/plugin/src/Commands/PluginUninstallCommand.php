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

#[AsCommand(name: 'plugin:uninstall', description: 'Uninstall a plugin (rollback migrations + remove row)')]
class PluginUninstallCommand extends PluginCommand
{
    protected $signature = 'plugin:uninstall
        {code : Plugin code}
        {--yes : Skip confirmation prompt}';

    protected $description = 'Uninstall a plugin (rollback migrations + remove row)';

    public function handle(): int
    {
        $code   = (string) $this->argument('code');
        $plugin = $this->resolvePlugin($code);

        if (! $plugin->checkInstalled()) {
            $this->warn("Not installed: {$code}");

            return self::SUCCESS;
        }

        if (! $this->option('yes') && ! $this->confirm("Uninstall {$code}? This rolls back migrations and deletes settings.")) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        $this->uninstall($plugin);
        $this->info("Uninstalled: {$code}");

        return self::SUCCESS;
    }
}
