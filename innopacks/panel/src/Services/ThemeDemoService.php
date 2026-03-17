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

class ThemeDemoService extends BaseService
{
    /**
     * Determine whether the theme has demo data
     * Supports: Seeder.php closure
     */
    public function hasDemo(string $dir): bool
    {
        // Check for Seeder.php closure (preferred)
        if (file_exists($dir.'/demo/Seeder.php')) {
            return true;
        }

        return false;
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
        // Check for PHP seeder
        $seederFile = $dir.'/demo/Seeder.php';
        if (! file_exists($seederFile)) {
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
     * Copy demo images to the public directory
     * @throws Exception
     */
    protected function copyDemoImages(string $dir): void
    {
        $pattern   = $dir.'/demo/images/**/*.{jpg,png,gif}';
        $images    = glob($pattern, GLOB_BRACE) ?: [];
        $themeCode = basename($dir);

        foreach ($images as $image) {
            if (! file_exists($image)) {
                continue;
            }

            $relativePath = str_replace($dir.'/demo/images/', '', $image);
            $targetPath   = public_path('static/themes/'.$themeCode.'/'.$relativePath);

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
        }
    }
}
