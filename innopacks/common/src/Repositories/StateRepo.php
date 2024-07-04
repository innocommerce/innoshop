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
use InnoShop\Common\Models\Country;
use InnoShop\Common\Models\State;

class StateRepo extends BaseRepo
{
    /**
     * Get filter builder.
     *
     * @param  $filters
     * @return Builder
     */
    public function builder($filters = []): Builder
    {
        $builder   = State::query();
        $countryID = $filters['country_id'] ?? 0;
        if ($countryID) {
            $builder->where('country_id', $countryID);
        }

        $countryCode = $filters['country_code'] ?? '';
        if ($countryCode) {
            $builder->where('country_code', $countryCode);
        }

        $code = $filters['code'] ?? '';
        if ($code) {
            $builder->where('code', $code);
        }

        return fire_hook_filter('repo.state.builder', $builder);
    }

    /**
     * @param  $data
     * @return mixed
     */
    public function create($data): mixed
    {
        $data = $this->handleData($data);

        return State::query()->create($data);
    }

    /**
     * @param  $items
     * @return void
     */
    public function createMany($items): void
    {
        $countries = [];
        foreach ($items as $item) {
            $countries[] = $this->handleData($item);
        }
        State::query()->insert($countries);
    }

    /**
     * @param  $requestData
     * @return array
     */
    public function handleData($requestData): array
    {
        $countryID   = $requestData['country_id']   ?? 0;
        $countryCode = $requestData['country_code'] ?? '';
        if (empty($countryID) && $countryCode) {
            $country   = Country::query()->where('code', $countryCode)->first();
            $countryID = $country->id ?? 0;
        }

        return [
            'country_id'   => $countryID,
            'country_code' => $countryCode,
            'name'         => $requestData['name'],
            'code'         => $requestData['code'],
            'position'     => $requestData['position']   ?? 0,
            'active'       => $requestData['active']     ?? true,
            'created_at'   => $requestData['created_at'] ?? now(),
            'updated_at'   => $requestData['updated_at'] ?? now(),
        ];
    }
}
