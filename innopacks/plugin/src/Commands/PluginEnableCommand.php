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

#[AsCommand(name: 'plugin:enable', description: 'Enable an installed plugin')]
class PluginEnableCommand extends PluginCommand
{
    protected $signature = 'plugin:enable {code : Plugin code}';

    protected $description = 'Enable an installed plugin';

    public function handle(): int
    {
        $code   = (string) $this->argument('code');
        $plugin = $this->resolvePlugin($code);

        if (! $plugin->checkInstalled()) {
            $this->error("Not installed: {$code}. Run: php artisan plugin:install {$code}");

            return self::FAILURE;
        }

        if ($plugin->checkActive()) {
            $this->warn("Already enabled: {$code}");

            return self::SUCCESS;
        }

        $this->setActive($plugin, true);
        $this->info("Enabled: {$code}");

        return self::SUCCESS;
    }
}
