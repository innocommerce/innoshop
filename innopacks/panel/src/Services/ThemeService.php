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
use Illuminate\Support\Facades\DB;
use InnoShop\Panel\Domain\Theme;
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
     * @return array
     * @throws Exception
     */
    public function getListFromPath(): array
    {
        $current = system_setting('theme');
        $dirs    = ThemeRepo::getInstance()->getThemeDirs();

        return collect($dirs)->map(function (string $dir) use ($current) {
            $config = ThemeRepo::getInstance()->readConfig($dir);
            $this->validateConfig($config);
            $this->validateCode($config, strtolower(basename($dir)));
            $theme = new Theme(
                code: $config['code'],
                names: $config['name'],
                descriptions: $config['description'],
                version: $config['version'],
                icon: $config['icon'],
                author: $config['author'],
                path: $dir,
                hasDemo: ThemeDemoService::getInstance()->hasDemo($dir),
                demoPath: ThemeDemoService::getInstance()->getDemoPath($dir),
                selected: ($config['code'] === $current),
                preview: $this->getPreviewPath($config['code'])
            );

            return $theme->toArray();
        })->all();
    }

    /**
     * Get all installed themes
     * @return array
     * @throws Exception
     */
    public function all(): array
    {
        return collect($this->getThemeDirs())
            ->map(fn (string $dir) => $this->readTheme($dir))
            ->map(fn (Theme $theme) => $theme->toArray())
            ->all();
    }

    /**
     * Get theme preview image path
     */
    public function getPreviewPath(string $themeCode): string
    {
        $base = $this->themesPath.'/'.$themeCode.'/public/images/';

        if (file_exists($base.'preview.png')) {
            return 'images/preview.png';
        }

        if (file_exists($base.'preview.jpg')) {
            return 'images/preview.jpg';
        }

        return '';
    }

    /**
     * Check if theme has demo data
     */
    public function hasDemo(string $dir): bool
    {
        $sqlFiles = glob($dir.'/demo/sql/*.sql');

        return ! empty($sqlFiles);
    }

    /**
     * Get list of theme directories
     */
    protected function getThemeDirs(): array
    {
        return glob($this->themesPath.'/*', GLOB_ONLYDIR) ?: [];
    }

    /**
     * Get demo SQL directory path
     */
    protected function getDemoPath(string $dir): string
    {
        return $dir.'/demo/sql';
    }

    /**
     * Read theme information from directory
     * @throws Exception
     */
    protected function readTheme(string $dir, ?string $folderName = null): Theme
    {
        $config = $this->readConfig($dir);
        $this->validateConfig($config);
        $this->validateCode($config, $folderName ?: strtolower(basename($dir)));

        return Theme::fromArray([
            ...$config,
            'path'      => $dir,
            'has_demo'  => $this->hasDemo($dir),
            'demo_path' => $this->getDemoPath($dir),
        ]);
    }

    /**
     * Read theme configuration file
     * @throws Exception
     */
    protected function readConfig(string $dir): array
    {
        $configFile = $dir.'/config.json';
        if (! file_exists($configFile)) {
            throw new Exception(__('panel/themes.error_config_not_found', ['file' => $configFile]));
        }

        $config = json_decode(file_get_contents($configFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception(__('panel/themes.error_config_invalid', ['file' => $configFile]));
        }

        return $config;
    }

    /**
     * Validate theme configuration fields
     * @throws Exception
     */
    protected function validateConfig(array $config): void
    {
        $required = ['code', 'name', 'description', 'version', 'icon', 'author'];
        foreach ($required as $key) {
            if (! isset($config[$key])) {
                throw new Exception(__('panel/themes.error_missing_field', ['field' => $key]));
            }
        }
    }

    /**
     * Validate theme code
     * @throws Exception
     */
    protected function validateCode(array $config, string $folderName): void
    {
        if (! isset($config['code']) || strtolower($config['code']) !== $folderName) {
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
     * Import SQL file
     *
     * @throws Exception
     */
    protected function importSqlFile(string $file): void
    {
        $sql = file_get_contents($file);
        if (! $sql) {
            throw new Exception(__('panel/themes.error_demo_sql_empty'));
        }

        // Split SQL statements
        $queries = array_filter(
            array_map('trim',
                explode(';', $sql)
            )
        );

        // Execute each SQL query
        foreach ($queries as $query) {
            if (! empty($query)) {
                DB::statement($query);
            }
        }
    }

    /**
     * Copy demo images to public directory
     */
    protected function copyDemoImages(string $dir): void
    {
        $pattern = $dir.'/demo/images/**/*.{jpg,png,gif}';
        $images  = glob($pattern, GLOB_BRACE) ?: [];

        // Get theme code
        $themeCode = basename($dir);

        // Copy images
        foreach ($images as $image) {
            $relativePath = str_replace($dir.'/demo/images/', '', $image);
            $targetPath   = public_path('static/themes/'.$themeCode.'/'.$relativePath);

            // Create target directory
            if (! is_dir(dirname($targetPath))) {
                mkdir(dirname($targetPath), 0755, true);
            }

            // Copy file
            copy($image, $targetPath);
        }
    }
}
