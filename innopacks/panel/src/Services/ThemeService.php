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
use Illuminate\Support\Facades\Log;
use InnoShop\Panel\Repositories\ThemeRepo;

class ThemeService extends BaseService
{
    protected string $themesPath;

    public function __construct(?string $themesPath = null)
    {
        $this->themesPath = $themesPath ?: base_path('themes');
    }

    /**
     * Get all themes with preview and selected status
     * @return array{themes: array, errors: array}
     * @throws Exception
     */
    public function getListFromPath(): array
    {
        $current = system_setting('theme');
        $dirs    = ThemeRepo::getInstance()->getThemeDirs();
        $errors  = [];

        $themes = collect($dirs)->map(function (string $dir) use ($current, &$errors) {
            $folderName = basename($dir);
            $themeName  = $folderName;
            $themeCode  = strtolower($folderName);

            try {
                $config = ThemeRepo::getInstance()->readConfig($dir);

                // Try to get theme name for better error messages
                if (isset($config['name'])) {
                    $localeCode = locale_code();
                    $themeName  = is_array($config['name'])
                        ? ($config['name'][$localeCode] ?? $config['name']['en'] ?? $folderName)
                        : $config['name'];
                }

                $this->validateConfig($config);
                $this->validateCode($config, $themeCode);

                // Return theme data
                return [
                    'code'     => $themeCode,
                    'name'     => $themeName,
                    'selected' => $current === $themeCode,
                    'preview'  => $this->getPreviewPath($dir),
                    'has_demo' => $this->hasDemo($dir),
                    'version'  => $config['version'] ?? '',
                    'author'   => $config['author'] ?? [],
                ];
            } catch (Exception $e) {
                Log::warning("Theme validation failed: {$e->getMessage()}", [
                    'directory' => $folderName,
                    'path'      => $dir,
                ]);
                $errors[] = [
                    'name'  => $themeName,
                    'error' => $e->getMessage(),
                ];

                return null;
            }
        })->filter()->values();

        return [
            'themes' => $themes,
            'errors' => $errors,
        ];
    }

    /**
     * Get theme preview image path
     */
    public function getPreviewPath(string $dir): string
    {
        // Check public/images/preview.jpg (standard location)
        $previewImage = $dir.'/public/images/preview.jpg';
        if (file_exists($previewImage)) {
            return 'images/preview.jpg';
        }

        // Check images/preview.jpg (alternative location)
        $previewImage = $dir.'/images/preview.jpg';
        if (file_exists($previewImage)) {
            return 'images/preview.jpg';
        }

        // Check preview.jpg in root (simple location)
        $previewImage = $dir.'/preview.jpg';
        if (file_exists($previewImage)) {
            return 'preview.jpg';
        }

        return '';
    }

    /**
     * Check if theme has demo data
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
     * Run theme's demo data installation
     *
     * @throws Exception
     */
    public function runDemoSeeder(string $dir): void
    {
        ThemeDemoService::getInstance()->importDemo($dir);
    }

    /**
     * Validate theme configuration
     * @throws Exception
     */
    protected function validateConfig(array $config): void
    {
        $required = ['code', 'name', 'version'];
        foreach ($required as $field) {
            if (! isset($config[$field])) {
                throw new Exception(__('panel/themes.error_config_missing', ['field' => $field]));
            }
        }
    }

    /**
     * Validate theme code matches folder name (must be lowercase)
     * @throws Exception
     */
    protected function validateCode(array $config, string $folderName): void
    {
        if ($config['code'] !== $folderName) {
            throw new Exception(__('panel/themes.error_code_mismatch', [
                'folder' => $folderName,
                'code'   => $config['code'],
            ]));
        }

        if ($config['code'] !== strtolower($config['code'])) {
            throw new Exception(__('panel/themes.error_code_not_lowercase', [
                'code' => $config['code'],
            ]));
        }
    }
}
