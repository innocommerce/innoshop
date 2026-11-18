<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Repositories\LocaleRepo;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Throwable;

class TranslationService extends BaseService
{
    const HAS_TRANSLATION = [
        'article', 'attribute_group', 'attribute', 'attribute_value', 'catalog', 'category', 'customer_group', 'page',
        'product', 'tag',
    ];

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function createLocale($data): mixed
    {
        return DB::transaction(function () use ($data) {
            $language = LocaleRepo::getInstance()->create($data);

            // Honour the "Autofill language" switch (panel settings, unchecked by
            // default): only copy default-locale content when it is enabled.
            if (system_setting('auto_fill_lang', false)) {
                $this->copyTranslations(setting_locale_code(), $language->code);
            }

            return $language;
        });
    }

    /**
     * Copy all description rows of one locale into another via a single
     * server-side INSERT ... SELECT per translation table, avoiding loading
     * every row (e.g. 12k+ product translations) into PHP memory.
     *
     * @param  string  $fromLocale
     * @param  string  $toLocale
     * @return void
     */
    public function copyTranslations(string $fromLocale, string $toLocale): void
    {
        $connection = DB::connection();

        foreach ($this->getDescriptionModels() as $className) {
            $model = new $className;
            $table = $model->getTable();

            $columns  = array_values(array_diff(Schema::getColumnListing($table), ['id', 'locale', 'created_at', 'updated_at']));
            $select   = $columns;
            $select[] = DB::raw($connection->getPdo()->quote($toLocale).' as locale');

            $insertColumns = array_merge($columns, ['locale']);
            foreach (['created_at', 'updated_at'] as $timeColumn) {
                if (Schema::hasColumn($table, $timeColumn)) {
                    $insertColumns[] = $timeColumn;
                    $select[]        = DB::raw("CURRENT_TIMESTAMP as {$timeColumn}");
                }
            }

            DB::table($table)->insertUsing($insertColumns, DB::table($table)->select($select)->where('locale', $fromLocale));
        }
    }

    /**
     * @param  $locale
     * @return void
     */
    public function deleteLocale($locale): void
    {
        if ($locale->code == system_setting('front_locale')) {
            throw new NotAcceptableHttpException(panel_trans('language.default_locale_cannot_delete'));
        }
        $locale->delete();

        foreach ($this->getDescriptionModels() as $className) {
            $className::query()->where('locale', $locale->code)->delete();
        }
    }

    /**
     * @return array
     */
    public function getDescriptionModels(): array
    {
        $items = [];
        foreach (self::HAS_TRANSLATION as $item) {
            $parts     = explode('_', $item);
            $namespace = implode('\\', array_map('ucfirst', $parts));
            $items[]   = "InnoShop\\Common\\Models\\$namespace\\Translation";
        }

        return $items;
    }
}
