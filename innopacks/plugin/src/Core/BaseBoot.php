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

    /**
     * Add a dynamic relation to a model.
     * This method uses the enhanced helper function add_model_relation().
     *
     * @param  string  $modelClass  The model class name (e.g., Product::class)
     * @param  string  $relationName  The name of the relation (e.g., 'seller')
     * @param  \Closure  $callback  The closure that defines the relation
     * @param  array  $options  Additional options (see add_model_relation() for details)
     * @return bool Returns true if relation was added successfully
     */
    protected function addModelRelation(string $modelClass, string $relationName, \Closure $callback, array $options = []): bool
    {
        return add_model_relation($modelClass, $relationName, $callback, $options);
    }

    /**
     * Add multiple relations to a model at once.
     *
     * @param  string  $modelClass  The model class name
     * @param  array  $relations  Array of relations: ['relationName' => Closure, ...]
     * @param  array  $options  Options passed to add_model_relation
     * @return array Returns array of results: ['relationName' => bool, ...]
     */
    protected function addModelRelations(string $modelClass, array $relations, array $options = []): array
    {
        return add_model_relations($modelClass, $relations, $options);
    }
}
