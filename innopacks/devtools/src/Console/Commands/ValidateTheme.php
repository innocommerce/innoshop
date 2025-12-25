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
use InnoShop\DevTools\Services\ValidationService;

class ValidateTheme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:validate-theme {path : Path to the theme directory}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate theme structure and configuration';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $path    = $this->argument('path');
        $service = new ValidationService;

        // Resolve relative paths
        if (! $this->isAbsolutePath($path)) {
            $path = base_path($path);
        }

        $this->info("Validating theme: {$path}...");

        $result = $service->validateTheme($path);

        if ($result['valid']) {
            $this->info('✓ Theme validation passed!');

            if (! empty($result['warnings'])) {
                $this->warn('Warnings:');
                foreach ($result['warnings'] as $warning) {
                    $this->line("  - {$warning}");
                }
            }

            return Command::SUCCESS;
        } else {
            $this->error('✗ Theme validation failed!');

            if (! empty($result['errors'])) {
                $this->error('Errors:');
                foreach ($result['errors'] as $error) {
                    $this->line("  - {$error}");
                }
            }

            if (! empty($result['warnings'])) {
                $this->warn('Warnings:');
                foreach ($result['warnings'] as $warning) {
                    $this->line("  - {$warning}");
                }
            }

            return Command::FAILURE;
        }
    }

    /**
     * Check if path is absolute.
     *
     * @param  string  $path
     * @return bool
     */
    private function isAbsolutePath(string $path): bool
    {
        return strpos($path, '/') === 0 || (PHP_OS_FAMILY === 'Windows' && preg_match('/^[A-Z]:\\\\/', $path));
    }
}
