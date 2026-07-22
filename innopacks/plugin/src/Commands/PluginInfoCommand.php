<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Commands;

use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: 'plugin:info', description: 'Show plugin details (config + state + path)')]
class PluginInfoCommand extends PluginCommand
{
    protected $signature = 'plugin:info {code : Plugin code}';

    protected $description = 'Show plugin details (config + state + path)';

    public function handle(): int
    {
        $code   = (string) $this->argument('code');
        $plugin = $this->resolvePlugin($code);

        $configPath = $plugin->getPath().'/config.json';
        $config     = is_file($configPath) ? json_decode((string) file_get_contents($configPath), true) : null;

        $panelRoute = '';
        try {
            $panelRoute = $plugin->getPanelRouteFromConfig();
        } catch (\Throwable) {
        }

        $this->line("<options=bold>{$plugin->getCode()}</> <fg=gray>({$plugin->getDirname()}/)</>  ".$this->stateLabel($plugin));
        $this->line('  type:        <fg=cyan>'.$plugin->getType().'</>');
        $this->line('  version:     '.$plugin->getVersion());
        $this->line('  path:        '.$plugin->getPath());
        $this->line('  author:      '.$plugin->getAuthorName().' <'.$plugin->getAuthorEmail().'>');
        if ($panelRoute) {
            $this->line('  panel route: '.$panelRoute);
        }
        $this->line('  name:        '.json_encode($plugin->getName(), JSON_UNESCAPED_UNICODE));
        $this->line('  description: '.json_encode($plugin->getDescription(), JSON_UNESCAPED_UNICODE));

        $rows = DB::table('settings')
            ->where('space', $code)
            ->orWhere('name', 'like', $code.'.%')
            ->get(['space', 'name', 'value', 'json']);

        if ($rows->isNotEmpty()) {
            $this->newLine();
            $this->line('<options=bold>Settings:</>');
            $tableRows = [];
            foreach ($rows as $r) {
                $tableRows[] = [$r->space, $r->name, $r->value, $r->json ? 'Y' : ''];
            }
            $this->table(['space', 'name', 'value', 'json'], $tableRows);
        }

        return self::SUCCESS;
    }
}
