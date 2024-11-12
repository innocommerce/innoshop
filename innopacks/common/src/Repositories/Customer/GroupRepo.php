<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Customer;

use InnoShop\Common\Models\Customer\Group;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Common\Resources\CustomerGroupSimple;

class GroupRepo extends BaseRepo
{
    /**
     * @return array[]
     */
    public static function getCriteria(): array
    {
        return [
            ['name' => 'name', 'type' => 'input', 'label' => trans('panel/common.name')],
            ['name' => 'level', 'type' => 'input', 'label' => trans('panel/customer.level')],
            ['name' => 'discount_rate', 'type' => 'input', 'label' => trans('panel/customer.discount_rate')],
            ['name'     => 'mini_cost', 'type' => 'range', 'label' => trans('panel/customer.mini_cost'),
                'start' => ['name' => 'start'],
                'end'   => ['name' => 'end'],
            ],
        ];
    }

    /**
     * @param  $data
     * @return mixed
     * @throws \Throwable
     */
    public function create($data): mixed
    {
        $group = new Group($this->handleData($data));
        $group->saveOrFail();

        $translations = $this->handleTranslations($data);
        $group->translations()->delete();
        $group->translations()->createMany($translations);

        return $group;
    }

    /**
     * @return array
     */
    public function getSimpleList(): array
    {
        $groups = GroupRepo::getInstance()->all();

        return CustomerGroupSimple::collection($groups)->jsonSerialize();
    }

    /**
     * @param  $requestData
     * @return array
     */
    private function handleData($requestData): array
    {
        return [
            'level'         => (int) $requestData['level'],
            'mini_cost'     => (float) $requestData['mini_cost'],
            'discount_rate' => (int) $requestData['discount_rate'],
        ];
    }

    /**
     * @param  $requestData
     * @return array
     */
    private function handleTranslations($requestData): array
    {
        $items = [];
        foreach ($requestData['name'] as $locale => $item) {
            $items[$locale]['locale'] = $locale;
            $items[$locale]['name']   = $item;
        }
        foreach ($requestData['description'] as $locale => $item) {
            $items[$locale]['description'] = $item;
        }

        return array_values($items);
    }
}
