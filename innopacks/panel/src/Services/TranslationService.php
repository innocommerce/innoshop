<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Services;

use InnoShop\Common\Repositories\LocaleRepo;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

class TranslationService extends BaseService
{
    const HAS_TRANSLATION = [
        'article', 'attribute_group', 'attribute', 'attribute_value', 'catalog', 'category', 'customer_group', 'page',
        'product', 'tag',
    ];

    /**
     * @param  $data
     * @return mixed
     */
    public function createLocale($data): mixed
    {
        $language      = LocaleRepo::getInstance()->create($data);
        $defaultLocale = setting_locale_code();
        foreach ($this->getDescriptionModels() as $className) {
            $items = $className::query()->where('locale', $defaultLocale)->get()->toArray();
            foreach ($items as &$item) {
                if (isset($item['created_at'])) {
                    $item['created_at'] = now();
                }
                if (isset($item['updated_at'])) {
                    $item['updated_at'] = now();
                }
                unset($item['id']);
                $item['locale'] = $language->code;
            }
            $className::query()->insert($items);
        }

        return $language;
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
