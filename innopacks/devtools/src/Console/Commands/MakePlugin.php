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

class MakePlugin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:make-plugin {name : The name of the plugin (PascalCase)} {--type=feature : Plugin type (feature/marketing/billing/etc)} {--with-controller : Generate a controller} {--with-model : Generate a model} {--with-migration : Generate a migration file} {--name-zh= : Chinese name} {--name-en= : English name} {--description-zh= : Chinese description} {--description-en= : English description}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new plugin scaffold';

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
            'type'            => $this->option('type'),
            'with_controller' => $this->option('with-controller'),
            'with_model'      => $this->option('with-model'),
            'with_migration'  => $this->option('with-migration'),
            'name_zh'         => $this->option('name-zh'),
            'name_en'         => $this->option('name-en'),
            'description_zh'  => $this->option('description-zh'),
            'description_en'  => $this->option('description-en'),
        ];

        try {
            $this->info("Creating plugin: {$name}...");

            $service->generatePlugin($name, $options);

            $this->info('Plugin created successfully!');
            $this->line('Plugin path: plugins/'.\Illuminate\Support\Str::studly($name));

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to create plugin: {$e->getMessage()}");

            return Command::FAILURE;
        }
    }
}
