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
use InnoShop\DevTools\Services\ScaffoldService;

class MakeTheme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:make-theme {name : The name of the theme (snake_case)} {--name-zh= : Chinese name} {--name-en= : English name} {--description-zh= : Chinese description} {--description-en= : English description}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new theme scaffold';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $name    = $this->argument('name');
        $service = new ScaffoldService;

        $options = [
            'name_zh'        => $this->option('name-zh'),
            'name_en'        => $this->option('name-en'),
            'description_zh' => $this->option('description-zh'),
            'description_en' => $this->option('description-en'),
        ];

        try {
            $this->info("Creating theme: {$name}...");

            $service->generateTheme($name, $options);

            $this->info('Theme created successfully!');
            $this->line('Theme path: themes/'.\Illuminate\Support\Str::snake($name));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create theme: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
