<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

class BaseService
{
    protected static $instance;

    /**
     * Get instance
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * Get singleton instance
     */
    public static function getSingleton(): static
    {
        if (static::$instance) {
            return static::$instance;
        }

        return static::$instance = new static;
    }
}
