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
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use InnoShop\Common\Models\Locale;

class LocaleRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('common/base.name')],
            ['name' => 'code', 'type' => 'input', 'label' => trans('panel/currency.code')],
            ['name' => 'status', 'type' => 'input', 'label' => trans('common/base.status')],
        ];
    }

    /**
     * Get search field options for data_search component
     *
     * @return array
     */
    public static function getSearchFieldOptions(): array
    {
        $options = [
            ['value' => '', 'label' => trans('panel/common.all_fields')],
            ['value' => 'name', 'label' => trans('common/base.name')],
            ['value' => 'code', 'label' => trans('panel/currency.code')],
        ];

        return fire_hook_filter('common.repo.locale.search_field_options', $options);
    }

    /**
     * Get filter button options for data_search component
     *
     * @return array
     */
    public static function getFilterButtonOptions(): array
    {
        $filters = [
            [
                'name'    => 'active',
                'label'   => trans('panel/common.status'),
                'type'    => 'button',
                'options' => [
                    ['value' => '', 'label' => trans('panel/common.all')],
                    ['value' => '1', 'label' => trans('panel/common.active_yes')],
                    ['value' => '0', 'label' => trans('panel/common.active_no')],
                ],
            ],
        ];

        return fire_hook_filter('common.repo.locale.filter_button_options', $filters);
    }

    public static ?Collection $enabledLocales = null;

    /**
     * https://lingohub.com/blog/right-to-left-vs-left-to-right
     *
     * Get all RTL languages.
     * @return string[]
     */
    public static function getRtlLanguages(): array
    {
        return [
            'ar'  => 'Arabic',
            'arc' => 'Aramaic',
            'dv	' => 'Divehi',
            'fa	' => 'Persian',
            'ha	' => 'Hausa',
            'he	' => 'Hebrew',
            'khw' => 'Khowar',
            'ks	' => 'Kashmiri',
            'ku	' => 'Kurdish',
            'ps	' => 'Pashto',
            'ur	' => 'Urdu',
            'yi	' => 'Yiddish',
        ];
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        return Locale::query()->create($data);
    }

    /**
     * @throws Exception
     */
    public function getFrontListWithPath(): array
    {
        $languages = Locale::all()->keyBy('code')->toArray();

        $result = [];
        foreach (front_lang_path_codes() as $localeCode) {
            $langFile = lang_path("/$localeCode/common/base.php");
            if (! is_file($langFile)) {
                throw new Exception("File ($langFile) not exist!");
            }
            $baseData = require $langFile;
            $name     = $baseData['name'] ?? $localeCode;
            $result[] = [
                'code'     => $localeCode,
                'name'     => $name,
                'id'       => $languages[$localeCode]['id'] ?? 0,
                'image'    => $languages[$localeCode]['image'] ?? "images/flag/$localeCode.png",
                'position' => $languages[$localeCode]['position'] ?? 0,
                'active'   => $languages[$localeCode]['active'] ?? true,
            ];
        }

        return $result;
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function builder(array $filters = []): Builder
    {
        $builder = Locale::query();

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', $code);
        }

        if (isset($filters['active'])) {
            $builder->where('active', (bool) $filters['active']);
        }

        // Handle new search filters (keyword + search_field)
        $keyword     = $filters['keyword'] ?? '';
        $searchField = $filters['search_field'] ?? '';
        if ($keyword && $searchField) {
            $builder->where($searchField, 'like', "%{$keyword}%");
        } elseif ($keyword) {
            $builder->where(function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('code', 'like', "%{$keyword}%");
            });
        }

        return fire_hook_filter('repo.locale.builder', $builder);
    }

    /**
     * Get active list.
     *
     * @return mixed
     * @throws Exception
     */
    public function getActiveList(): mixed
    {
        if (self::$enabledLocales !== null && self::$enabledLocales->isNotEmpty()) {
            return self::$enabledLocales;
        }

        return self::$enabledLocales = $this->builder(['active' => true])->orderBy('position')->get();
    }
}
