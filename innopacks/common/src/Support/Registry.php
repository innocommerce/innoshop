<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Support;

class Registry
{
    /**
     * Registry data storage
     *
     * @var array
     */
    protected static array $data = [];

    /**
     * Set a value in the registry
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    public static function set(string $key, mixed $value): void
    {
        static::$data[$key] = $value;
    }

    /**
     * Get a value from the registry
     *
     * @param  string  $key
     * @param  mixed|null  $default
     * @return mixed
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return static::$data[$key] ?? $default;
    }

    /**
     * Check if a key exists in the registry
     *
     * @param  string  $key
     * @return bool
     */
    public static function has(string $key): bool
    {
        return isset(static::$data[$key]);
    }

    /**
     * Remove a value from the registry
     *
     * @param  string  $key
     * @return void
     */
    public static function remove(string $key): void
    {
        unset(static::$data[$key]);
    }

    /**
     * Clear all data from the registry
     *
     * @return void
     */
    public static function clear(): void
    {
        static::$data = [];
    }

    /**
     * Get all data from the registry
     *
     * @return array
     */
    public static function all(): array
    {
        return static::$data;
    }
}
