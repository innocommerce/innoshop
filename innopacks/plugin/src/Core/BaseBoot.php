<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Core;

use InnoShop\Plugin\Resources\PluginResource;

abstract class BaseBoot
{
    protected Plugin $plugin;

    protected PluginResource $pluginResource;

    public function __construct()
    {
        $className            = static::class;
        $names                = explode('\\', $className);
        $spaceName            = $names[1];
        $this->plugin         = app('plugin')->getPlugin($spaceName);
        $this->pluginResource = new PluginResource($this->plugin);
    }

    abstract public function init();
}
