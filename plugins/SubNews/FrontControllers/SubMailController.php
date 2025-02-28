<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SubNews\FrontControllers;

use Illuminate\Http\JsonResponse;
use InnoShop\Front\Controllers\BaseController;
use Plugin\SubNews\Repositories\SubMailRepo;
use Plugin\SubNews\Requests\SubMailRequest;
use Throwable;

class SubMailController extends BaseController
{
    /**
     * @param  SubMailRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function subscribe(SubMailRequest $request): JsonResponse
    {
        try {
            $data = $request->all();

            $data['customer_id'] = current_customer_id();
            SubMailRepo::getInstance()->create($data);

            return create_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
