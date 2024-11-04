<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Country;
use InnoShop\Common\Repositories\CountryRepo;
use InnoShop\Common\Repositories\StateRepo;
use InnoShop\Common\Resources\CountrySimple;
use InnoShop\Panel\Controllers\BaseController;

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
     * @param  string  $code
     * @return mixed
     */
    public function show(string $code): mixed
    {
        $country = Country::query()->where('code', $code)->orWhere('id', $code)->first();
        if (empty($country)) {
            return collect();
        }

        $filters = [
            'country_id' => $country->id,
        ];
        $countries = StateRepo::getInstance()->builder($filters)->get();

        return CountrySimple::collection($countries);
    }
}
