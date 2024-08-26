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
    public static ?Collection $enabledLocales = null;

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
            $langFile = lang_path("/$localeCode/front/base.php");
            if (! is_file($langFile)) {
                throw new Exception("File ($langFile) not exist!");
            }
            $baseData = require $langFile;
            $name     = $baseData['name'] ?? $localeCode;
            $result[] = [
                'code'     => $localeCode,
                'name'     => $name,
                'id'       => $languages[$localeCode]['id']       ?? 0,
                'image'    => $languages[$localeCode]['image']    ?? "images/flag/$localeCode.png",
                'position' => $languages[$localeCode]['position'] ?? 0,
                'active'   => $languages[$localeCode]['active']   ?? true,
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
        if (self::$enabledLocales !== null) {
            return self::$enabledLocales;
        }

        return self::$enabledLocales = $this->builder(['active' => true])->orderBy('position')->get();
    }
}
