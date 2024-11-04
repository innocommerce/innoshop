<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Country;
use InnoShop\Common\Repositories\CountryRepo;
use InnoShop\Common\Repositories\StateRepo;
use InnoShop\Common\Resources\CountrySimple;
use InnoShop\Common\Resources\StateItem;

class CountryController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $countries = CountryRepo::getInstance()->builder($request->all())->get();

        return CountrySimple::collection($countries);
    }

    /**
     * @param  Country  $country
     * @return AnonymousResourceCollection
     */
    public function states(Country $country): AnonymousResourceCollection
    {
        $filters = [
            'country_id' => $country->id,
        ];
        $states = StateRepo::getInstance()->builder($filters)->get();

        return StateItem::collection($states);
    }
}
