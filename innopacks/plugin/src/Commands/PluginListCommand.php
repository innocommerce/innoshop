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

#[AsCommand(name: 'plugin:list', description: 'List all InnoShop plugins (with install / active state)')]
class PluginListCommand extends PluginCommand
{
    protected $signature = 'plugin:list
        {--installed : Only show installed plugins}
        {--enabled : Only show enabled plugins}
        {--available : Only show plugins not yet installed}';

    protected $description = 'List all InnoShop plugins (with install / active state)';

    public function handle(): int
    {
        $plugins = app('plugin')->getPlugins();
        $values  = array_values($plugins->all());

        if ($this->option('installed')) {
            $values = array_filter($values, fn ($p) => $p->checkInstalled());
        } elseif ($this->option('enabled')) {
            $values = array_filter($values, fn ($p) => $p->checkActive());
        } elseif ($this->option('available')) {
            $values = array_filter($values, fn ($p) => ! $p->checkInstalled());
        }

        usort($values, fn ($a, $b) => strcmp($a->getCode(), $b->getCode()));

        if (empty($values)) {
            $this->warn('(no plugins match)');

            return self::SUCCESS;
        }

        $this->tablePlugins($values);
        $this->line('Total: <info>'.count($values).'</info> plugin(s)');

        return self::SUCCESS;
    }
}
