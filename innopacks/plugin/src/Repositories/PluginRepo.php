<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Plugin\Models\Plugin;

class PluginRepo
{
    public static Collection $installedPlugins;

    public function __construct()
    {
        self::$installedPlugins = new Collection;
    }

    /**
     * @return self
     */
    public static function getInstance(): PluginRepo
    {
        return new self;
    }

    /**
     * 获取所有已安装插件列表
     *
     * @return Collection
     */
    public function allPlugins(): Collection
    {
        if (self::$installedPlugins->count() > 0) {
            return self::$installedPlugins;
        }

        return self::$installedPlugins = Plugin::all();
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function getBuilder(array $filters = []): Builder
    {
        $builder = Plugin::query();
        $type    = $filters['type'] ?? '';
        if ($type) {
            $builder->where('type', $type);
        }

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', $code);
        }

        return fire_hook_filter('repo.plugin.builder', $builder);
    }

    /**
     * Group plugins by code.
     *
     * @return Collection
     */
    public function getPluginsGroupCode(): Collection
    {
        $allPlugins = $this->allPlugins();

        return $allPlugins->keyBy('code');
    }

    /**
     * @param  $code
     * @return mixed
     */
    public function getPluginByCode($code): mixed
    {
        return $this->getPluginsGroupCode()->get($code);
    }

    /**
     * Check plugin installed or not.
     * @param  $code
     * @return bool
     */
    public function installed($code): bool
    {
        return $this->getPluginsGroupCode()->has($code);
    }

    /**
     * Get plugin active
     *
     * @param  $pluginCode
     * @return bool
     */
    public function checkActive($pluginCode): bool
    {
        return (bool) setting("{$pluginCode}.active");
    }

    /**
     * Get plugin priority
     *
     * @param  $pluginCode
     * @return int
     */
    public function getPriority($pluginCode): int
    {
        $plugin = $this->getPluginByCode($pluginCode);
        if (empty($plugin)) {
            return 0;
        }

        return (int) $plugin->priority;
    }

    /**
     * Get all shipping methods.
     */
    public function getShippingMethods(): Collection
    {
        $allPlugins = $this->allPlugins();

        return $allPlugins->where('type', 'shipping')->filter(function ($item) {
            $plugin = plugin($item->code);
            if ($plugin) {
                $item->plugin = $plugin;
            }

            return $plugin && $plugin->getEnabled();
        });
    }

    /**
     * Get all billing methods.
     */
    public function getBillingMethods(): Collection
    {
        $allPlugins = $this->allPlugins();

        return $allPlugins->where('type', 'billing')->filter(function ($item) {
            $plugin = plugin($item->code);
            if ($plugin) {
                $item->plugin = $plugin;
            }

            $available = plugin_setting($item->code, 'available', []);
            if (is_wechat_mini()) {
                $flag = in_array('wechat_mini', $available);
            } elseif (is_wechat_official()) {
                $flag = in_array('wechat_official', $available);
            } elseif (is_mobile()) {
                $flag = in_array('mobile_web', $available);
            } elseif (is_app()) {
                $flag = in_array('app', $available);
            } else {
                $flag = in_array('pc_web', $available);
            }

            return $plugin && $plugin->getEnabled() && $flag;
        });
    }
}
