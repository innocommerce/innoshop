<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Core;

use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InnoShop\Plugin\Traits\CleansUpExtractedFiles;

class PluginManager
{
    use CleansUpExtractedFiles;

    /**
     * Cache key for the parsed plugins config (mtime + data payload).
     * Lives in the default Cache store so `php artisan cache:clear` flushes it.
     */
    public const PLUGINS_CONFIG_CACHE_KEY = 'innoshop.plugins.config';

    protected static ?Collection $plugins = null;

    /**
     * Diagnostics from the most recent loadCachedPluginsConfig() call.
     * Values: 'hit' | 'miss_no_data' | 'miss_stale' | 'skipped_debug' | null.
     * Static so any caller (including the panel view) can read it after the
     * singleton has parsed config, without depending on instance identity.
     */
    protected static ?string $lastCacheStatus = null;

    /**
     * @return string|null Cache status from the last getPlugins() call.
     */
    public static function getLastCacheStatus(): ?string
    {
        return self::$lastCacheStatus;
    }

    /**
     * Get all plugins.
     *
     * @return Collection
     * @throws Exception
     */
    public function getPlugins(): Collection
    {
        if (self::$plugins !== null) {
            return self::$plugins;
        }

        $existed       = $this->getPluginsConfig();
        $plugins       = new Collection;
        $hiddenPlugins = $this->getHiddenPluginsFromEnv();

        foreach ($existed as $dirname => $package) {
            if ($package['hide'] ?? false) {
                continue;
            }

            // Check if plugin code is in hidden plugins list from .env
            $pluginCode = $package['code'] ?? '';
            if (in_array($pluginCode, $hiddenPlugins)) {
                continue;
            }

            $normalizedDirname = strtolower(Str::snake($dirname));
            $normalizedCode    = strtolower(Str::snake($pluginCode));

            if ($normalizedDirname !== $normalizedCode) {
                Log::warning("Plugin code mismatch: Directory '{$dirname}' does not match config.json code '{$pluginCode}'. Plugin will be skipped.", [
                    'directory' => $dirname,
                    'code'      => $pluginCode,
                    'path'      => $this->getPluginsDir().DIRECTORY_SEPARATOR.$dirname,
                ]);

                continue;
            }

            $pluginPath = $this->getPluginsDir().DIRECTORY_SEPARATOR.$dirname;

            // Check plugin type before creating plugin instance (apply legacy type mapping)
            $pluginType     = $package['type'] ?? '';
            $normalizedType = strtolower($pluginType);
            $normalizedType = Plugin::TYPE_MAPPING[$normalizedType] ?? $normalizedType;
            if (! in_array($normalizedType, Plugin::TYPES)) {
                Log::warning("Plugin invalid type: Directory '{$dirname}' has invalid type '{$pluginType}'. Plugin will be skipped.", [
                    'directory'   => $dirname,
                    'type'        => $pluginType,
                    'path'        => $pluginPath,
                    'valid_types' => Plugin::TYPES,
                ]);

                continue;
            }
            // Normalize type to lowercase for consistency
            $package['type'] = $normalizedType;

            try {
                $plugin = new Plugin($pluginPath, $package);
            } catch (Exception $e) {
                Log::error('The Plugin: '.$dirname.' - '.$e->getMessage());

                continue;
            }

            $plugin->setCode($package['code']);
            $plugin->setType($package['type']);
            $plugin->setName($package['name']);
            $plugin->setDescription($package['description']);
            $plugin->setAuthor($package['author'] ?? []);
            $plugin->setIcon($package['icon'] ?? null);
            $plugin->setVersion($package['version']);
            $plugin->setDirname($dirname);
            $plugin->setInstalled(true);
            $plugin->setEnabled($plugin->checkActive());
            $plugin->setPriority($plugin->checkPriority());
            $plugin->setFields();

            $code = $plugin->getCode();
            if ($plugins->has($code)) {
                continue;
            }
            $plugins->put($code, $plugin);
        }

        self::$plugins = $plugins->sortBy(function ($plugin) {
            return $plugin->getPriority();
        });

        return self::$plugins;
    }

    /**
     * Get all enabled plugins.
     *
     * @return Collection
     * @throws Exception
     */
    public function getEnabledPlugins(): Collection
    {
        $allPlugins = $this->getPlugins();

        return $allPlugins->filter(function (Plugin $plugin) {
            return $plugin->checkInstalled() && $plugin->getEnabled();
        });
    }

    /**
     * Get single plugin by code
     *
     * @throws Exception
     */
    public function getPlugin($code): ?Plugin
    {
        $plugins = $this->getPlugins();

        if (isset($plugins[$code])) {
            return $plugins[$code];
        }

        $code = Str::snake($code);

        return $plugins[$code] ?? null;
    }

    /**
     * Get single plugin by code or thrown exception.
     *
     * @throws Exception
     */
    public function getPluginOrFail($code): ?Plugin
    {
        $plugin = $this->getPlugin($code);
        if (empty($plugin)) {
            throw new Exception('Invalid plugin!');
        }
        $plugin->handleLabel();

        return $plugin;
    }

    /**
     * Check plugin is active, include existed, installed and enabled
     *
     * @param  $code
     * @return bool
     * @throws Exception
     */
    public function checkActive($code): bool
    {
        $plugin = $this->getPlugin($code);
        if (empty($plugin) || ! $plugin->checkInstalled() || ! $plugin->getEnabled()) {
            return false;
        }

        return true;
    }

    /**
     * Get hidden plugins from .env configuration.
     *
     * @return array
     */
    protected function getHiddenPluginsFromEnv(): array
    {
        $hiddenPlugins = env('HIDDEN_PLUGINS', '');
        if (empty($hiddenPlugins)) {
            return [];
        }

        return array_map('trim', explode(',', $hiddenPlugins));
    }

    /**
     * Get plugin config.
     *
     * @return array
     */
    protected function getPluginsConfig(): array
    {
        $pluginsDir = $this->getPluginsDir();
        if (! is_dir($pluginsDir)) {
            return [];
        }

        if ($this->loadCachedPluginsConfig($pluginsDir, $installed)) {
            return $installed;
        }

        $installed = $this->scanPluginsDirectory($pluginsDir);
        $this->writeCachedPluginsConfig($pluginsDir, $installed);

        return $installed;
    }

    /**
     * Try to load cached plugin config. Returns false on miss / invalidation.
     * Cache invalidates when any plugin's config.json mtime changes (edit /
     * add / remove / rename subdir). Skipped entirely when APP_DEBUG=true to
     * surface config edits immediately.
     */
    protected function loadCachedPluginsConfig(string $pluginsDir, ?array &$installed): bool
    {
        if (config('app.debug')) {
            self::$lastCacheStatus = 'skipped_debug';

            return false;
        }

        $cached = Cache::get(self::PLUGINS_CONFIG_CACHE_KEY);
        if (! is_array($cached) || ! isset($cached['mtimes'], $cached['data'])) {
            self::$lastCacheStatus = 'miss_no_data';

            return false;
        }

        $currentMtimes = $this->scanConfigMtimes($pluginsDir);
        if ($currentMtimes !== $cached['mtimes']) {
            self::$lastCacheStatus = 'miss_stale';

            return false;
        }

        self::$lastCacheStatus = 'hit';
        $installed             = $cached['data'];

        return true;
    }

    /**
     * Persist scanned plugin config to the default Cache store, tagged with
     * the current mtimes of every plugin's config.json so any edit triggers
     * a rescan on next read. No-op when APP_DEBUG=true to surface config
     * edits immediately.
     *
     * @param  string  $pluginsDir  Absolute path to plugins/ directory.
     * @param  array  $installed  Plugin config list returned by {@see scanPluginsDirectory()}.
     * @return void
     */
    protected function writeCachedPluginsConfig(string $pluginsDir, array $installed): void
    {
        if (config('app.debug')) {
            return;
        }

        $mtimes = $this->scanConfigMtimes($pluginsDir);

        Cache::forever(self::PLUGINS_CONFIG_CACHE_KEY, [
            'mtimes' => $mtimes,
            'data'   => $installed,
        ]);
    }

    /**
     * Build a map of [plugin_dir => config.json mtime] for cache invalidation.
     * Cheap: stat() only, no file reads. Detects edits to existing config.json
     * (mtime changes), new plugins (new key), removed plugins (missing key),
     * and renames (key swap).
     *
     * @return array<string, int>
     */
    protected function scanConfigMtimes(string $pluginsDir): array
    {
        if (! is_dir($pluginsDir)) {
            return [];
        }

        $mtimes = [];
        foreach ((array) scandir($pluginsDir) as $subdir) {
            if ($subdir === '.' || $subdir === '..') {
                continue;
            }
            $configPath = $pluginsDir.DIRECTORY_SEPARATOR.$subdir.DIRECTORY_SEPARATOR.'config.json';
            if (! is_file($configPath)) {
                continue;
            }
            $mtime = @filemtime($configPath);
            if ($mtime !== false) {
                $mtimes[$subdir] = $mtime;
            }
        }

        return $mtimes;
    }

    /**
     * Scan plugins/ directory and parse every subdir's config.json.
     * Returns a sorted list keyed by directory name. Subdirs without a
     * valid config.json are logged and skipped (install state preserved
     * by dirname keys so the install/uninstall flow stays consistent).
     *
     * @param  string  $pluginsDir  Absolute path to plugins/ directory (must exist).
     * @return array<string, array> Map of [dirname => config.json array], sorted by code.
     */
    protected function scanPluginsDirectory(string $pluginsDir): array
    {
        $installed = [];
        $resource  = opendir($pluginsDir);
        while ($filename = @readdir($resource)) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $path = $pluginsDir.DIRECTORY_SEPARATOR.$filename;
            if (is_dir($path)) {
                $packageJsonPath = $path.DIRECTORY_SEPARATOR.'config.json';
                if (file_exists($packageJsonPath)) {
                    $config = json_decode(file_get_contents($packageJsonPath), true);
                    if ($config) {
                        $installed[$filename] = $config;
                    } else {
                        $jsonError = json_last_error_msg();
                        Log::warning("Plugin config.json parse failed: Directory '{$filename}' has invalid JSON. Plugin will be skipped.", [
                            'directory' => $filename,
                            'path'      => $packageJsonPath,
                            'error'     => $jsonError,
                        ]);
                    }
                } else {
                    Log::warning("Plugin missing config.json: Directory '{$filename}' exists but has no config.json file. Plugin will be skipped.", [
                        'directory' => $filename,
                        'path'      => $path,
                    ]);
                }
            }
        }
        closedir($resource);

        return collect($installed)->sortBy(function ($item) {
            return strtolower($item['code'] ?? '');
        })->all();
    }

    /**
     * Return plugin root directory.
     *
     * @return string
     */
    protected function getPluginsDir(): string
    {
        return config('plugins.directory') ?: base_path('plugins');
    }

    /**
     * Upload plugin and unzip it.
     *
     * Extraction uses a temporary directory so {@see cleanupExtractedFiles} does not run on the
     * entire <code>plugins/</code> tree — doing that recursively removed every sibling plugin's
     * <code>.git</code> directory.
     *
     * @throws Exception
     */
    public function import(UploadedFile $file): void
    {
        $originalName = $file->getClientOriginalName();
        $destPath     = storage_path('upload');
        $newFilePath  = $destPath.'/'.$originalName;
        $file->move($destPath, $originalName);

        $this->extractZipAndMergeIntoRoot($newFilePath, base_path('plugins'));
    }
}
