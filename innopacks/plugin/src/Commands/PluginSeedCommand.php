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

#[AsCommand(name: 'plugin:seed', description: 'Run Database/Seeders/*.php for a plugin')]
class PluginSeedCommand extends PluginCommand
{
    protected $signature = 'plugin:seed {code : Plugin code}';

    protected $description = 'Run Database/Seeders/*.php for a plugin';

    public function handle(): int
    {
        $code   = (string) $this->argument('code');
        $plugin = $this->resolvePlugin($code);

        $result = $this->runSeeders($plugin);
        $this->info("Ran {$result['ran']}/{$result['total']} seeder(s) for {$code}");
        foreach ($result['errors'] as $error) {
            $this->error('  '.$error);
        }

        return $result['errors'] === [] ? self::SUCCESS : self::FAILURE;
    }
}
