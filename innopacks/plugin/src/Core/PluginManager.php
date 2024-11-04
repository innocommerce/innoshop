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
use PhpZip\ZipFile;

class PluginManager
{
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

        $existed = $this->getPluginsConfig();
        $plugins = new Collection;
        foreach ($existed as $dirname => $package) {
            if ($package['hide'] ?? false) {
                continue;
            }

            $pluginPath = $this->getPluginsDir().DIRECTORY_SEPARATOR.$dirname;

            try {
                $plugin = new Plugin($pluginPath, $package);
            } catch (\Exception $e) {
                Log::error('The Plugin: '.$dirname.' - '.$e->getMessage());

                continue;
            }

            $plugin->setCode($package['code']);
            $plugin->setType($package['type']);
            $plugin->setName($package['name']);
            $plugin->setDescription($package['description']);
            $plugin->setAuthor($package['author']['name'].'('.$package['author']['email'].')');
            $plugin->setIcon($package['icon']);
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
        $code    = Str::snake($code);
        $plugins = $this->getPlugins();

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
     * Get plugin config.
     *
     * @return array
     */
    protected function getPluginsConfig(): array
    {
        $installed = [];
        $resource  = opendir($this->getPluginsDir());
        while ($filename = @readdir($resource)) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }
            $path = $this->getPluginsDir().DIRECTORY_SEPARATOR.$filename;
            if (is_dir($path)) {
                $packageJsonPath = $path.DIRECTORY_SEPARATOR.'config.json';
                if (file_exists($packageJsonPath)) {
                    $installed[$filename] = json_decode(file_get_contents($packageJsonPath), true);
                }
            }
        }
        closedir($resource);

        return $installed;
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
     * @throws Exception
     */
    public function import(UploadedFile $file): void
    {
        $originalName = $file->getClientOriginalName();
        $destPath     = storage_path('upload');
        $newFilePath  = $destPath.'/'.$originalName;
        $file->move($destPath, $originalName);

        $zipFile = new ZipFile;
        $zipFile->openFile($newFilePath)->extractTo(base_path('plugins'));
    }
}
