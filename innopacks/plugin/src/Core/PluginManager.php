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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InnoShop\Plugin\Traits\CleansUpExtractedFiles;

class PluginManager
{
    use CleansUpExtractedFiles;

    protected static ?Collection $plugins = null;

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
     * Cache invalidates when plugins/ dir mtime changes (add/remove/rename subdir).
     * Skipped entirely when APP_DEBUG=true to surface config edits immediately.
     */
    protected function loadCachedPluginsConfig(string $pluginsDir, ?array &$installed): bool
    {
        if (config('app.debug')) {
            return false;
        }

        $cacheFile = $this->getPluginsCacheFile();
        if (! is_file($cacheFile)) {
            return false;
        }

        $cached = @json_decode((string) file_get_contents($cacheFile), true);
        if (! is_array($cached) || ! isset($cached['mtime'], $cached['data'])) {
            return false;
        }

        $dirMtime = @filemtime($pluginsDir);
        if ($dirMtime === false || $cached['mtime'] !== $dirMtime) {
            return false;
        }

        $installed = $cached['data'];

        return true;
    }

    /**
     * Persist scanned plugin config to disk, tagged with current plugins/ dir mtime
     * so {@see loadCachedPluginsConfig()} can detect drift on next read.
     * No-op when APP_DEBUG=true to surface config edits immediately.
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

        $dirMtime = @filemtime($pluginsDir);
        if ($dirMtime === false) {
            return;
        }

        $cacheFile = $this->getPluginsCacheFile();
        @file_put_contents(
            $cacheFile,
            json_encode(['mtime' => $dirMtime, 'data' => $installed], JSON_PRETTY_PRINT)
        );
    }

    /**
     * Absolute path to the plugin config cache file.
     * Lives next to Laravel's framework cache (storage/framework/).
     *
     * @return string
     */
    protected function getPluginsCacheFile(): string
    {
        return storage_path('framework/plugins-cache.json');
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
