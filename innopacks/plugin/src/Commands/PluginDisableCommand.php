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

#[AsCommand(name: 'plugin:disable', description: 'Disable an installed plugin')]
class PluginDisableCommand extends PluginCommand
{
    protected $signature = 'plugin:disable {code : Plugin code}';

    protected $description = 'Disable an installed plugin';

    public function handle(): int
    {
        $code   = (string) $this->argument('code');
        $plugin = $this->resolvePlugin($code);

        if (! $plugin->checkInstalled()) {
            $this->warn("Not installed: {$code}");

            return self::SUCCESS;
        }

        if (! $plugin->checkActive()) {
            $this->warn("Already disabled: {$code}");

            return self::SUCCESS;
        }

        $this->setActive($plugin, false);
        $this->info("Disabled: {$code}");

        return self::SUCCESS;
    }
}
