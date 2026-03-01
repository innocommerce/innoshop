<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\PluginCoordination;
use InnoShop\Plugin\Repositories\PluginRepo;

class PluginCoordinationService
{
    /**
     * Cache expiration time in seconds (1 hour).
     */
    private const CACHE_TTL = 3600;

    /**
     * Get plugins ordered by configuration.
     *
     * @param  string  $type  Plugin type (price, orderfee)
     * @return Collection Ordered collection of plugins
     */
    public function getOrderedPlugins(string $type): Collection
    {
        $config  = $this->getConfig($type);
        $plugins = $this->getPluginsByType($type);

        if ($config?->sort_order && is_array($config->sort_order) && count($config->sort_order) > 0) {
            return $plugins->sortBy(function ($plugin) use ($config) {
                $position = array_search($plugin->code, $config->sort_order);

                return $position === false ? 999 : $position;
            })->values();
        }

        return $plugins;
    }

    /**
     * Get coordination configuration for a specific plugin type.
     *
     * @param  string  $type  Plugin type
     * @return PluginCoordination|null
     */
    public function getConfig(string $type): ?PluginCoordination
    {
        $cacheKey = $this->getCacheKey($type);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($type) {
            return PluginCoordination::where('type', $type)->first();
        });
    }

    /**
     * Check if the current plugin should skip execution based on coordination rules.
     *
     * @param  string  $type  Plugin type
     * @param  string  $current  Current plugin code
     * @param  array  $appliedPlugins  List of already applied plugin codes
     * @return bool True if the plugin should skip execution
     */
    public function shouldSkip(string $type, string $current, array $appliedPlugins): bool
    {
        if (empty($appliedPlugins)) {
            return false;
        }

        $config = $this->getConfig($type);

        if (! $config) {
            return false;
        }

        return match ($config->exclusive_mode) {
            'first_only' => true,  // Already have applied plugins, skip all others
            'all_stack'  => false, // All plugins can stack, never skip
            'custom'     => $this->hasExclusiveConflict($config, $current, $appliedPlugins),
            default      => false,
        };
    }

    /**
     * Check if the current plugin has an exclusive conflict with any applied plugin.
     *
     * @param  PluginCoordination  $config  Configuration object
     * @param  string  $current  Current plugin code
     * @param  array  $appliedPlugins  List of already applied plugin codes
     * @return bool True if there's a conflict
     */
    public function hasExclusiveConflict(PluginCoordination $config, string $current, array $appliedPlugins): bool
    {
        $pairs = $config->exclusive_pairs ?? [];

        if (empty($pairs)) {
            return false;
        }

        foreach ($pairs as $pair) {
            if (is_array($pair) && in_array($current, $pair)) {
                // Current plugin is in an exclusive pair
                // Check if any applied plugin is also in this pair
                foreach ($appliedPlugins as $applied) {
                    if (in_array($applied, $pair)) {
                        return true; // Conflict detected
                    }
                }
            }
        }

        return false;
    }

    /**
     * Clear coordination cache for a specific type.
     *
     * @param  string|null  $type  Plugin type, or null for all types
     * @return void
     */
    public function clearCache(?string $type = null): void
    {
        if ($type) {
            Cache::forget($this->getCacheKey($type));
        } else {
            // Clear all plugin coordination caches
            $types = PluginCoordination::pluck('type')->toArray();
            foreach ($types as $type) {
                Cache::forget($this->getCacheKey($type));
            }
        }
    }

    /**
     * Get cache key for a specific plugin type.
     *
     * @param  string  $type
     * @return string
     */
    private function getCacheKey(string $type): string
    {
        return "plugin_coordination:{$type}";
    }

    /**
     * Get plugins by type from the plugin repository.
     *
     * @param  string  $type
     * @return Collection
     */
    private function getPluginsByType(string $type): Collection
    {
        $pluginRepo = PluginRepo::getInstance();

        return $pluginRepo->getBuilder(['type' => $type])->get();
    }

    /**
     * Create or update coordination configuration for a plugin type.
     *
     * @param  string  $type
     * @param  array  $data
     * @return PluginCoordination
     */
    public function updateConfig(string $type, array $data): PluginCoordination
    {
        $config = PluginCoordination::updateOrCreate(
            ['type' => $type],
            [
                'sort_order'      => $data['sort_order'] ?? [],
                'exclusive_mode'  => $data['exclusive_mode'] ?? 'all_stack',
                'exclusive_pairs' => $data['exclusive_pairs'] ?? [],
            ]
        );

        $this->clearCache($type);

        return $config;
    }

    /**
     * Delete coordination configuration for a plugin type.
     *
     * @param  string  $type
     * @return bool
     */
    public function deleteConfig(string $type): bool
    {
        $deleted = PluginCoordination::where('type', $type)->delete();

        if ($deleted) {
            $this->clearCache($type);
        }

        return (bool) $deleted;
    }
}
