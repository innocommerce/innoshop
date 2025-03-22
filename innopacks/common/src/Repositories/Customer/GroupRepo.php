<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Customer;

use Exception;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Handlers\TranslationHandler;
use InnoShop\Common\Models\Customer\Group;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Common\Resources\CustomerGroupSimple;
use Throwable;

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
            ['name' => 'mini_cost', 'type' => 'range', 'label' => trans('panel/customer.mini_cost')],
        ];
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $group = new Group;

        return $this->createOrUpdate($group, $data);
    }

    /**
     * @param  mixed  $item
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function update(mixed $item, $data): mixed
    {
        return $this->createOrUpdate($item, $data);
    }

    /**
     * @param  Group  $group
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    private function createOrUpdate(Group $group, $data): mixed
    {
        DB::beginTransaction();

        try {
            $groupData = $this->handleData($data);
            $group->fill($groupData);
            $group->saveOrFail();

            $translations = $this->handleTranslations($data);
            if ($translations) {
                $group->translations()->delete();
                $group->translations()->createMany($translations);
            }

            DB::commit();

            return $group;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
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
     * Process translations with TranslationHandler
     *
     * @param  array  $requestData
     * @return array
     */
    private function handleTranslations($requestData): array
    {
        if (empty($requestData['translations'])) {
            return [];
        }

        // Define field mapping if needed
        $fieldMap = [
            'name' => ['description'],
        ];

        // Process translations using TranslationHandler
        return TranslationHandler::process($requestData['translations'], $fieldMap);
    }
}
