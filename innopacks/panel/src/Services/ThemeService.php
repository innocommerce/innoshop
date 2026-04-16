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
            $localeCode = locale_code();

            try {
                $config = ThemeRepo::getInstance()->readConfig($dir);

                // Try to get theme name for better error messages
                if (isset($config['name'])) {
                    $themeName = is_array($config['name'])
                        ? ($config['name'][$localeCode] ?? $config['name']['en'] ?? $folderName)
                        : $config['name'];
                }

                // Get localized description
                $themeDescription = '';
                if (isset($config['description'])) {
                    $themeDescription = is_array($config['description'])
                        ? ($config['description'][$localeCode] ?? $config['description']['en'] ?? '')
                        : $config['description'];
                }

                $this->validateConfig($config);
                $this->validateCode($config, $themeCode);

                // Return theme data
                return [
                    'code'        => $themeCode,
                    'name'        => $themeName,
                    'description' => $themeDescription,
                    'selected'    => $current === $themeCode,
                    'preview'     => $this->getPreviewPath($dir),
                    'has_demo'    => ThemeDemoService::getInstance()->hasDemo($dir),
                    'version'     => $config['version'] ?? '',
                    'author'      => $config['author'] ?? [],
                ];
            } catch (Exception $e) {
                Log::warning("Theme validation failed: {$e->getMessage()}", [
                    'directory' => $folderName,
                    'path'      => $dir,
                ]);
                $errors[] = [
                    'name'   => $themeName,
                    'folder' => $folderName,
                    'error'  => $e->getMessage(),
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
        $extensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

        // Check public/images/preview.{ext} (standard location)
        foreach ($extensions as $ext) {
            $previewImage = $dir.'/public/images/preview.'.$ext;
            if (file_exists($previewImage)) {
                return 'images/preview.'.$ext;
            }
        }

        // Check images/preview.{ext} (alternative location)
        foreach ($extensions as $ext) {
            $previewImage = $dir.'/images/preview.'.$ext;
            if (file_exists($previewImage)) {
                return 'images/preview.'.$ext;
            }
        }

        // Check preview.{ext} in root (simple location)
        foreach ($extensions as $ext) {
            $previewImage = $dir.'/preview.'.$ext;
            if (file_exists($previewImage)) {
                return 'preview.'.$ext;
            }
        }

        return '';
    }

    /**
     * Check if theme has demo data (delegates to ThemeDemoService for one source of truth).
     */
    public function hasDemo(string $dir): bool
    {
        return ThemeDemoService::getInstance()->hasDemo($dir);
    }

    /**
     * Run theme's demo data installation
     *
     * @throws Exception
     */
    public function runDemoSeeder(string $dir, bool $clearDefaultCatalog = false): void
    {
        if ($clearDefaultCatalog) {
            ThemeDemoCatalogResetService::getInstance()->clearDefaultCatalogData();
        }

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
