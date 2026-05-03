<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CountryRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Countries')]
class CountryController extends BaseController
{
    #[Endpoint('List countries')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): mixed
    {
        try {
            $filters   = $request->all();
            $perPage   = $request->get('per_page', 15);
            $countries = CountryRepo::getInstance()->builder($filters)->paginate($perPage);

            return read_json_success($countries);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get country detail')]
    #[UrlParam('id', 'integer', description: 'Country ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $country = CountryRepo::getInstance()->builder()->findOrFail($id);

            return read_json_success($country);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
