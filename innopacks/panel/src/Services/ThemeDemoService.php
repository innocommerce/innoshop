<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

declare(strict_types=1);

namespace InnoShop\Panel\Services;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ThemeDemoService extends BaseService
{
    /**
     * Resolve the PHP file used for demo import (same rules as import).
     * Supports: demo/Seeder.php (any case basename), *Seeder.php, or exactly one demo/*.php.
     */
    public function resolveDemoSeederPath(string $dir): ?string
    {
        $demoDir = $dir.'/demo';
        if (! is_dir($demoDir)) {
            return null;
        }

        $phpFiles = glob($demoDir.'/*.php') ?: [];
        if ($phpFiles === []) {
            return null;
        }

        foreach ($phpFiles as $path) {
            if (strcasecmp(basename($path), 'Seeder.php') === 0) {
                return $path;
            }
        }

        foreach ($phpFiles as $path) {
            if (preg_match('/Seeder\.php$/i', basename($path))) {
                return $path;
            }
        }

        if (count($phpFiles) === 1) {
            return $phpFiles[0];
        }

        return null;
    }

    /**
     * Determine whether the theme has demo data (must match {@see importDemo}).
     */
    public function hasDemo(string $dir): bool
    {
        return $this->resolveDemoSeederPath($dir) !== null;
    }

    /**
     * Get the demo directory path
     */
    public function getDemoPath(string $dir): string
    {
        return $dir.'/demo';
    }

    /**
     * Import theme demo data (PHP Seeder + images)
     * @throws Exception
     */
    public function importDemo(string $dir): void
    {
        $seederFile = $this->resolveDemoSeederPath($dir);
        if ($seederFile === null || ! is_file($seederFile)) {
            throw new Exception(__('panel/themes.error_demo_not_found'));
        }

        $this->runPhpSeeder($dir, $seederFile);
    }

    /**
     * Run PHP seeder for theme demo data
     * @throws Exception
     */
    protected function runPhpSeeder(string $dir, string $seederFile): void
    {
        smart_log('info', '[ThemeDemo] Running PHP seeder', [
            'file' => $seederFile,
        ]);

        // Copy demo images first
        $this->copyDemoImages($dir);

        // Include and run the seeder
        $seeder = require $seederFile;

        if (! is_callable($seeder)) {
            throw new Exception(__('panel/themes.error_demo_seeder_invalid'));
        }

        try {
            $seeder($dir);
            smart_log('info', '[ThemeDemo] PHP seeder completed successfully');
        } catch (Exception $e) {
            smart_log('error', '[ThemeDemo] PHP seeder failed', [
                'error' => $e->getMessage(),
            ]);
            throw new Exception(__('panel/themes.error_demo_seeder_failed', [
                'error' => $e->getMessage(),
            ]));
        }
    }

    /**
     * Copy demo images to the public directory.
     * Copies both {@see $dir}/public/images (e.g. preview.png) and {@see $dir}/demo/images (e.g. figma/*.jpg)
     * when present — themes often keep only preview assets under public/images while bulk demo art lives in demo/images.
     *
     * @throws Exception
     */
    protected function copyDemoImages(string $dir): void
    {
        $themeCode       = basename($dir);
        $targetRelative  = 'static/themes/'.$themeCode.'/images';
        $publicImagesDir = $dir.'/public/images';
        $demoImagesDir   = $dir.'/demo/images';
        $total           = 0;

        if (is_dir($publicImagesDir)) {
            $total += $this->copyImagesFromSource($publicImagesDir, $targetRelative);
        }

        if (is_dir($demoImagesDir)) {
            $total += $this->copyImagesFromSource($demoImagesDir, $targetRelative);
        }

        if ($total === 0) {
            smart_log('warning', '[ThemeDemo] No demo images found in theme', [
                'theme' => $themeCode,
            ]);
        }
    }

    /**
     * Recursively copy image files from a source directory into public/{targetRelativePath}.
     *
     * @return int Number of files copied
     *
     * @throws Exception
     */
    protected function copyImagesFromSource(string $sourceDir, string $targetRelativePath): int
    {
        $sourceDir = rtrim($sourceDir, DIRECTORY_SEPARATOR);
        $allowed   = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
        $images    = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($sourceDir, FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, $allowed, true)) {
                continue;
            }
            $images[] = $file->getPathname();
        }

        $count = 0;
        foreach ($images as $image) {
            $relativePath = substr($image, strlen($sourceDir) + 1);
            $relativePath = str_replace(DIRECTORY_SEPARATOR, '/', $relativePath);
            $targetPath   = public_path($targetRelativePath.'/'.$relativePath);

            $targetDir = dirname($targetPath);
            if (! is_dir($targetDir)) {
                if (! mkdir($targetDir, 0755, true)) {
                    throw new Exception(__('panel/themes.error_demo_image_dir_failed', [
                        'dir' => $targetDir,
                    ]));
                }
            }

            if (! copy($image, $targetPath)) {
                throw new Exception(__('panel/themes.error_demo_image_copy_failed', [
                    'file' => basename($image),
                ]));
            }
            $count++;
        }

        smart_log('info', '[ThemeDemo] Images copied from source', [
            'count'  => $count,
            'source' => $sourceDir,
            'target' => $targetRelativePath,
        ]);

        return $count;
    }
}
