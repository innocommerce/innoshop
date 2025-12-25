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
use Illuminate\Support\Facades\File;
use InnoShop\DevTools\Services\MarketplaceService;
use InnoShop\DevTools\Services\PackageService;
use InnoShop\DevTools\Services\ValidationService;

class PublishTheme extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:publish-theme {path? : Path to the theme directory (default: current directory)} {--dry-run : Only create package, do not upload} {--skip-validation : Skip validation before publishing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Package and publish theme to marketplace';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $path = $this->argument('path') ?: getcwd();

        // Resolve relative paths
        if (! $this->isAbsolutePath($path)) {
            $path = base_path($path);
        }

        if (! File::exists($path)) {
            $this->error("Theme path does not exist: {$path}");

            return Command::FAILURE;
        }

        // Validate theme
        if (! $this->option('skip-validation')) {
            $this->info('Validating theme...');
            $validator = new ValidationService;
            $result    = $validator->validateTheme($path);

            if (! $result['valid']) {
                $this->error('Theme validation failed!');
                foreach ($result['errors'] as $error) {
                    $this->line("  - {$error}");
                }

                if (! $this->confirm('Continue anyway?', false)) {
                    return Command::FAILURE;
                }
            } else {
                $this->info('✓ Theme validation passed!');
            }
        }

        // Read config.json
        $configPath = "{$path}/config.json";
        if (! File::exists($configPath)) {
            $this->error('config.json not found!');

            return Command::FAILURE;
        }

        $config = json_decode(File::get($configPath), true);
        $this->info("Package: {$config['code']} v{$config['version']}");

        // Create package
        $this->info('Creating package...');
        $packageService = new PackageService;

        try {
            $zipPath = $packageService->createPackage($path, 'theme', ['config' => $config]);
            $this->info("Package created: {$zipPath}");

            if ($this->option('dry-run')) {
                $this->info('Dry-run mode: Package created but not uploaded.');

                return Command::SUCCESS;
            }

            // Upload to marketplace
            $this->info('Uploading to marketplace...');
            $marketplaceService = new MarketplaceService;
            $result             = $marketplaceService->upload($zipPath, 'theme', ['config' => $config]);

            $this->info('✓ Theme published successfully!');
            if (isset($result['message'])) {
                $this->line($result['message']);
            }

            // Cleanup
            $packageService->cleanup($zipPath);

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to publish theme: {$e->getMessage()}");

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
