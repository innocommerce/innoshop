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
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Models\Option;
use Throwable;

class OptionRepo extends BaseRepo
{
    protected string $model = Option::class;

    /**
     * Get paginated list of option groups
     *
     * @param  array  $filters
     * @return LengthAwarePaginator
     * @throws Exception
     */
    public function list(array $filters = []): LengthAwarePaginator
    {
        return $this->builder($filters)->orderBy('id', 'desc')->paginate();
    }

    /**
     * Get all option groups
     *
     * @param  array  $filters
     * @return Collection
     * @throws Exception
     */
    public function all(array $filters = []): Collection
    {
        return $this->builder($filters)->get();
    }

    /**
     * Build query builder and apply filter conditions
     *
     * @param  array  $filters
     * @return Builder
     * @throws Exception
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Option::query();

        $name = $filters['name'] ?? '';
        if ($name) {
            // Search directly in JSON field
            $builder->where(function ($query) use ($name) {
                $query->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.en')) LIKE ?", ["%{$name}%"])
                    ->orWhereRaw("JSON_UNQUOTE(JSON_EXTRACT(name, '$.zh-cn')) LIKE ?", ["%{$name}%"]);
            });
        }

        $type = $filters['type'] ?? '';
        if ($type) {
            $builder->where('type', $type);
        }

        $active = $filters['active'] ?? '';
        if ($active !== '') {
            $builder->where('active', (bool) $active);
        }

        return fire_hook_filter('repo.option.builder', $builder);
    }

    /**
     * Create new option group
     *
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $optionData = $this->handleData($data);

        // Handle multilingual data
        if (isset($data['translations'])) {
            $nameTranslations        = [];
            $descriptionTranslations = [];

            foreach ($data['translations'] as $locale => $translation) {
                // Only add name field if it has value
                if (! empty($translation['name'])) {
                    $nameTranslations[$locale] = $translation['name'];
                }
                // Add description field if exists, allow null values
                if (isset($translation['description'])) {
                    $descriptionTranslations[$locale] = $translation['description'];
                }
            }

            // Only override default values when translation data exists
            if (! empty($nameTranslations)) {
                $optionData['name'] = $nameTranslations;
            }
            if (! empty($descriptionTranslations)) {
                $optionData['description'] = $descriptionTranslations;
            }
        }

        $option = new Option($optionData);
        $option->saveOrFail();

        return $option->fresh();
    }

    /**
     * Update existing option group
     *
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update(mixed $item, $data): mixed
    {
        $option     = $item;
        $optionData = $this->handleData($data);

        // Handle multilingual data
        if (isset($data['translations'])) {
            $nameTranslations        = [];
            $descriptionTranslations = [];

            foreach ($data['translations'] as $locale => $translation) {
                // Only add name field if it has value
                if (! empty($translation['name'])) {
                    $nameTranslations[$locale] = $translation['name'];
                }
                // Add description field if exists, allow null values
                if (isset($translation['description'])) {
                    $descriptionTranslations[$locale] = $translation['description'];
                }
            }

            // Only override existing values when translation data exists
            if (! empty($nameTranslations)) {
                $optionData['name'] = $nameTranslations;
            }
            if (! empty($descriptionTranslations)) {
                $optionData['description'] = $descriptionTranslations;
            }
        }

        $option->fill($optionData);
        $option->saveOrFail();

        return $option->fresh();
    }

    /**
     * Delete option group
     *
     * @param  mixed  $item
     * @return void
     * @throws Throwable
     */
    public function destroy(mixed $item): void
    {
        $option = $item;
        $option->delete();
    }

    /**
     * Handle data
     *
     * @param  array  $data
     * @return array
     */
    private function handleData(array $data): array
    {
        return [
            'name'        => $data['name'] ?? [],
            'description' => $data['description'] ?? [],
            'type'        => $data['type'] ?? 'select',
            'position'    => $data['position'] ?? 0,
            'active'      => $data['active'] ?? true,
            'required'    => $data['required'] ?? false,
        ];
    }

    /**
     * Create translation records (deprecated, now using JSON fields for multilingual storage)
     *
     * @param  Option  $option
     * @param  array  $data
     * @return void
     * @throws Throwable
     */
    private function createTranslations(Option $option, array $data): void
    {
        $translations = $data['translations'] ?? [];
        foreach ($translations as $locale => $translation) {
            if (empty($translation['name'])) {
                continue;
            }

            $option->translations()->create([
                'locale' => $locale,
                'name'   => $translation['name'],
            ]);
        }
    }
}
