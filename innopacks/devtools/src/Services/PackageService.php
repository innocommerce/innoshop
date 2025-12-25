<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Services;

use Illuminate\Support\Facades\File;
use PhpZip\ZipFile;

class PackageService
{
    /**
     * Create package ZIP file.
     *
     * @param  string  $sourcePath
     * @param  string  $type
     * @param  array  $metadata
     * @return string
     */
    public function createPackage(string $sourcePath, string $type = 'plugin', array $metadata = []): string
    {
        if (! File::exists($sourcePath)) {
            throw new \RuntimeException("Source path does not exist: {$sourcePath}");
        }

        // Get package name from config.json
        $configPath = "{$sourcePath}/config.json";
        if (! File::exists($configPath)) {
            throw new \RuntimeException('config.json not found');
        }

        $config      = json_decode(File::get($configPath), true);
        $packageName = $config['code'] ?? basename($sourcePath);
        $version     = $config['version'] ?? 'v1.0.0';

        // Create temporary directory for package
        $tempDir = storage_path('app/temp_packages');
        File::ensureDirectoryExists($tempDir);

        $zipFileName = "{$packageName}-{$version}.zip";
        $zipPath     = "{$tempDir}/{$zipFileName}";

        // Create ZIP file
        $zip = new ZipFile;

        try {
            $files = $this->getFilesToPackage($sourcePath);

            foreach ($files as $file) {
                $relativePath = str_replace($sourcePath.'/', '', $file);
                $zip->addFile($file, $relativePath);
            }

            $zip->saveAsFile($zipPath);
            $zip->close();
        } catch (\Exception $e) {
            $zip->close();
            throw new \RuntimeException("Failed to create package: {$e->getMessage()}");
        }

        return $zipPath;
    }

    /**
     * Get files to package (excluding unwanted files).
     *
     * @param  string  $sourcePath
     * @return array
     */
    private function getFilesToPackage(string $sourcePath): array
    {
        $excludePatterns = config('devtools.exclude_patterns', []);
        $files           = [];
        $iterator        = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($sourcePath, \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $relativePath = str_replace($sourcePath.'/', '', $file->getPathname());

                // Check if file should be excluded
                $shouldExclude = false;
                foreach ($excludePatterns as $pattern) {
                    if ($this->matchesPattern($relativePath, $pattern)) {
                        $shouldExclude = true;
                        break;
                    }
                }

                if (! $shouldExclude) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }

    /**
     * Check if path matches pattern.
     *
     * @param  string  $path
     * @param  string  $pattern
     * @return bool
     */
    private function matchesPattern(string $path, string $pattern): bool
    {
        // Convert glob pattern to regex
        $pattern = str_replace(['*', '?'], ['.*', '.'], $pattern);
        $pattern = '#^'.$pattern.'$#';

        return preg_match($pattern, $path) === 1;
    }

    /**
     * Clean up temporary package files.
     *
     * @param  string  $zipPath
     * @return void
     */
    public function cleanup(string $zipPath): void
    {
        if (File::exists($zipPath)) {
            File::delete($zipPath);
        }
    }
}
