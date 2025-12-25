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

class ValidationService
{
    /**
     * Validate plugin structure.
     *
     * @param  string  $pluginPath
     * @return array
     */
    public function validatePlugin(string $pluginPath): array
    {
        $errors   = [];
        $warnings = [];

        // Check if path exists
        if (! File::exists($pluginPath)) {
            return [
                'valid'    => false,
                'errors'   => ["Plugin path does not exist: {$pluginPath}"],
                'warnings' => [],
            ];
        }

        // Check config.json
        $configPath = "{$pluginPath}/config.json";
        if (! File::exists($configPath)) {
            $errors[] = 'Missing config.json file';
        } else {
            $configErrors = $this->validateConfigJson($configPath);
            $errors       = array_merge($errors, $configErrors);
        }

        // Check Boot.php
        $bootPath = "{$pluginPath}/Boot.php";
        if (! File::exists($bootPath)) {
            $errors[] = 'Missing Boot.php file';
        } else {
            $bootErrors = $this->validateBootFile($bootPath);
            $errors     = array_merge($errors, $bootErrors);
        }

        // Check directory structure
        $dirErrors = $this->validateDirectoryStructure($pluginPath);
        $warnings  = array_merge($warnings, $dirErrors);

        // Check naming conventions
        $namingErrors = $this->validateNamingConventions($pluginPath);
        $warnings     = array_merge($warnings, $namingErrors);

        // Check language files
        $langErrors = $this->validateLanguageFiles($pluginPath);
        $warnings   = array_merge($warnings, $langErrors);

        return [
            'valid'    => empty($errors),
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate theme structure.
     *
     * @param  string  $themePath
     * @return array
     */
    public function validateTheme(string $themePath): array
    {
        $errors   = [];
        $warnings = [];

        // Check if path exists
        if (! File::exists($themePath)) {
            return [
                'valid'    => false,
                'errors'   => ["Theme path does not exist: {$themePath}"],
                'warnings' => [],
            ];
        }

        // Check config.json
        $configPath = "{$themePath}/config.json";
        if (! File::exists($configPath)) {
            $errors[] = 'Missing config.json file';
        } else {
            $configErrors = $this->validateThemeConfigJson($configPath);
            $errors       = array_merge($errors, $configErrors);
        }

        // Check required directories
        $requiredDirs = ['views', 'public'];
        foreach ($requiredDirs as $dir) {
            if (! File::isDirectory("{$themePath}/{$dir}")) {
                $warnings[] = "Missing recommended directory: {$dir}";
            }
        }

        return [
            'valid'    => empty($errors),
            'errors'   => $errors,
            'warnings' => $warnings,
        ];
    }

    /**
     * Validate config.json file.
     *
     * @param  string  $configPath
     * @return array
     */
    private function validateConfigJson(string $configPath): array
    {
        $errors = [];
        $config = json_decode(File::get($configPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'config.json is not valid JSON';

            return $errors;
        }

        $requiredFields = ['code', 'name', 'description', 'type', 'version', 'author'];
        foreach ($requiredFields as $field) {
            if (! isset($config[$field])) {
                $errors[] = "Missing required field in config.json: {$field}";
            }
        }

        // Validate plugin type
        if (isset($config['type'])) {
            $validTypes = config('devtools.plugin_types', []);
            if (! in_array($config['type'], $validTypes)) {
                $errors[] = "Invalid plugin type: {$config['type']}. Valid types: ".implode(', ', $validTypes);
            }
        }

        // Validate name structure
        if (isset($config['name']) && ! is_array($config['name'])) {
            $errors[] = 'config.json "name" must be an object with language keys';
        }

        // Validate description structure
        if (isset($config['description']) && ! is_array($config['description'])) {
            $errors[] = 'config.json "description" must be an object with language keys';
        }

        return $errors;
    }

    /**
     * Validate theme config.json file.
     *
     * @param  string  $configPath
     * @return array
     */
    private function validateThemeConfigJson(string $configPath): array
    {
        $errors = [];
        $config = json_decode(File::get($configPath), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $errors[] = 'config.json is not valid JSON';

            return $errors;
        }

        $requiredFields = ['code', 'name', 'description', 'version', 'author'];
        foreach ($requiredFields as $field) {
            if (! isset($config[$field])) {
                $errors[] = "Missing required field in config.json: {$field}";
            }
        }

        return $errors;
    }

    /**
     * Validate Boot.php file.
     *
     * @param  string  $bootPath
     * @return array
     */
    private function validateBootFile(string $bootPath): array
    {
        $errors  = [];
        $content = File::get($bootPath);

        // Check namespace
        if (strpos($content, 'namespace') === false) {
            $errors[] = 'Boot.php must have a namespace declaration';
        }

        // Check class extends BaseBoot
        if (strpos($content, 'extends BaseBoot') === false) {
            $errors[] = 'Boot.php class must extend BaseBoot';
        }

        // Check init method
        if (strpos($content, 'public function init()') === false) {
            $errors[] = 'Boot.php must have a public init() method';
        }

        return $errors;
    }

    /**
     * Validate directory structure.
     *
     * @param  string  $pluginPath
     * @return array
     */
    private function validateDirectoryStructure(string $pluginPath): array
    {
        $warnings        = [];
        $recommendedDirs = ['Controllers', 'Models', 'Services', 'Routes', 'Views', 'Lang'];

        foreach ($recommendedDirs as $dir) {
            if (! File::isDirectory("{$pluginPath}/{$dir}")) {
                $warnings[] = "Missing recommended directory: {$dir}";
            }
        }

        return $warnings;
    }

    /**
     * Validate naming conventions.
     *
     * @param  string  $pluginPath
     * @return array
     */
    private function validateNamingConventions(string $pluginPath): array
    {
        $warnings   = [];
        $pluginName = basename($pluginPath);

        // Check if plugin name follows PascalCase
        if (! preg_match('/^[A-Z][a-zA-Z0-9]*$/', $pluginName)) {
            $warnings[] = "Plugin name '{$pluginName}' should follow PascalCase convention";
        }

        return $warnings;
    }

    /**
     * Validate language files.
     *
     * @param  string  $pluginPath
     * @return array
     */
    private function validateLanguageFiles(string $pluginPath): array
    {
        $warnings = [];
        $langPath = "{$pluginPath}/Lang";

        if (! File::isDirectory($langPath)) {
            $warnings[] = 'Missing Lang directory';

            return $warnings;
        }

        $requiredLangs = ['en', 'zh-cn'];
        foreach ($requiredLangs as $lang) {
            $langDir = "{$langPath}/{$lang}";
            if (! File::isDirectory($langDir)) {
                $warnings[] = "Missing language directory: {$lang}";
            }
        }

        return $warnings;
    }
}
