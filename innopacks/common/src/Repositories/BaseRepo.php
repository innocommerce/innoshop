<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories;

use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class BaseRepo implements RepoInterface
{
    protected string $model;

    protected string $table;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        if (empty($this->model)) {
            $classPath   = str_replace('Repositories', 'Models', static::class);
            $this->model = str_replace('Repo', '', $classPath);
        }

        if (! class_exists($this->model)) {
            throw new Exception("Cannot find the model: $this->model!");
        }
        $this->table = (new $this->model)->getTable();
    }

    /**
     * @return static
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->paginate();
    }

    /**
     * @throws Exception
     */
    public function all(array $filters = []): Collection
    {
        return $this->builder($filters)->get();
    }

    /**
     * @param  int  $id
     * @return mixed
     * @throws Exception
     */
    public function detail(int $id): mixed
    {
        return $this->modelQuery()->find($id);
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Exception
     */
    public function create($data): mixed
    {
        return $this->modelQuery()->create($data);
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     * @throws Exception
     */
    public function update(mixed $item, $data): mixed
    {
        if (is_int($item)) {
            $item = $this->modelQuery()->find($item);
        }
        if ($item) {
            $item->update($data);
        }

        return $item;
    }

    /**
     * @param  mixed  $item
     * @throws Exception
     */
    public function destroy(mixed $item): void
    {
        if (is_int($item)) {
            $item = $this->modelQuery()->find($item);
        }
        if ($item) {
            $item->delete();
        }
    }

    /**
     * @param  array  $filters
     * @return Builder
     * @throws Exception
     */
    public function builder(array $filters = []): Builder
    {
        return $this->modelQuery();
    }

    /**
     * Get all columns from current table
     *
     * @return array
     */
    public function getColumns(): array
    {
        return Schema::getColumnListing($this->table);
    }

    /**
     * @return Builder
     * @throws Exception
     */
    private function modelQuery(): Builder
    {
        return $this->model::query();
    }
}
