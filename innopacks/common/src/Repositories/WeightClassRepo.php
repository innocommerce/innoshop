<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use InnoShop\Common\Models\WeightClass;

class WeightClassRepo extends BaseRepo
{
    private static ?WeightClassRepo $instance = null;

    /**
     * Get singleton instance
     *
     * @return static
     */
    public static function getInstance(): static
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Get model class name
     *
     * @return string
     */
    protected function getModel(): string
    {
        return WeightClass::class;
    }

    /**
     * Get all weight classes
     *
     * @param  array  $filters  Filter conditions
     * @return Collection
     */
    public function all(array $filters = []): Collection
    {
        return $this->remember('all', function () {
            return WeightClass::orderBy('position')->get();
        });
    }

    /**
     * Get active weight classes
     *
     * @return static
     */
    public function withActive(): static
    {
        $this->filters['active'] = true;

        return $this;
    }

    /**
     * Find weight class by code
     *
     * @param  string  $code  Weight class code
     * @return mixed
     */
    public function findByCode(string $code)
    {
        return $this->remember("code_$code", function () use ($code) {
            return WeightClass::where('code', $code)->first();
        });
    }

    /**
     * Get default weight class code
     *
     * @return string
     */
    public function getDefaultWeightClassCode(): string
    {
        return system_setting('default_weight_class', 'g');
    }

    /**
     * Get list of enabled weight classes ordered by position
     *
     * @return mixed
     */
    public function enabledList(): mixed
    {
        return $this->builder(['active' => true])
            ->orderBy('position')
            ->get();
    }

    /**
     * Get base query builder with filters
     *
     * @param  array  $filters  Query filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = WeightClass::query();

        if (isset($filters['active'])) {
            $builder->where('active', $filters['active']);
        }

        if (isset($filters['code'])) {
            $builder->where('code', $filters['code']);
        }

        if (isset($filters['search'])) {
            $builder->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%{$filters['search']}%")
                    ->orWhere('code', 'like', "%{$filters['search']}%")
                    ->orWhere('unit', 'like', "%{$filters['search']}%");
            });
        }

        return $builder;
    }

    /**
     * Cache and remember the result
     *
     * @param  string  $key  Cache key
     * @param  \Closure  $callback  Callback function to execute if key is not found
     * @param  int  $minutes  Minutes to cache
     * @return mixed
     */
    protected function remember(string $key, \Closure $callback, int $minutes = 30)
    {
        return Cache::remember('weight_class_'.$key, $minutes * 60, $callback);
    }
}
