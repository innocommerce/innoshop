<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Models;

class PluginCoordination extends BaseModel
{
    protected $table = 'plugin_coordination';

    protected $fillable = [
        'type',
        'sort_order',
        'exclusive_mode',
        'exclusive_pairs',
    ];

    protected $casts = [
        'sort_order'      => 'array',
        'exclusive_pairs' => 'array',
    ];

    /**
     * Get the sort order as an array.
     *
     * @return array
     */
    public function getSortOrder(): array
    {
        return $this->sort_order ?? [];
    }

    /**
     * Get the exclusive mode.
     *
     * @return string
     */
    public function getExclusiveMode(): string
    {
        return $this->exclusive_mode ?? 'all_stack';
    }

    /**
     * Get the exclusive pairs.
     *
     * @return array
     */
    public function getExclusivePairs(): array
    {
        return $this->exclusive_pairs ?? [];
    }

    /**
     * Check if the exclusive mode is first_only.
     *
     * @return bool
     */
    public function isFirstOnlyMode(): bool
    {
        return $this->exclusive_mode === 'first_only';
    }

    /**
     * Check if the exclusive mode is all_stack.
     *
     * @return bool
     */
    public function isAllStackMode(): bool
    {
        return $this->exclusive_mode === 'all_stack';
    }

    /**
     * Check if the exclusive mode is custom.
     *
     * @return bool
     */
    public function isCustomMode(): bool
    {
        return $this->exclusive_mode === 'custom';
    }
}
