<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use InnoShop\Common\Models\Country;
use InnoShop\Common\Models\State;

class AddressListItem extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     */
    public function toArray(Request $request): array
    {
        $countryID  = $this->country_id;
        $countryRow = Country::query()->find($countryID);

        $stateID  = $this->state_id;
        $stateRow = State::query()->find($stateID);

        return [
            'id'           => $this->id,
            'customer_id'  => $this->customer_id,
            'guest_id'     => $this->guest_id,
            'name'         => $this->name,
            'email'        => $this->email,
            'phone'        => $this->phone,
            'country_id'   => $this->country_id,
            'country_code' => $countryRow->code ?? '',
            'country_name' => $countryRow->name ?? '',
            'state_id'     => $stateID,
            'state_code'   => $stateRow->code ?? '',
            'state_name'   => $stateRow->name ?? '',
            'state'        => $this->state,
            'city_id'      => $this->city_id,
            'city'         => $this->city,
            'zipcode'      => $this->zipcode,
            'address_1'    => $this->address_1,
            'address_2'    => $this->address_2,
        ];
    }
}
