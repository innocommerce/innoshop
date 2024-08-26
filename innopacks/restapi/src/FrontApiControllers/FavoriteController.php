<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\Customer\FavoriteRepo;
use InnoShop\Common\Resources\FavoriteItem;

class FavoriteController extends BaseController
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $filters = [
            'customer_id' => token_customer_id(),
        ];
        $favorites = FavoriteRepo::getInstance()->list($filters);

        return FavoriteItem::collection($favorites);
    }

    /**
     * Add to favorite list.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = [
                'customer_id' => token_customer_id(),
                'product_id'  => $request->get('product_id'),
            ];
            FavoriteRepo::getInstance()->create($data);

            return json_success(front_trans('common.saved_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Destroy favorite item.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        try {
            $customerID = token_customer_id();
            $filters    = [
                'customer_id' => $customerID,
                'product_id'  => $request->get('product_id'),
            ];

            $favorite = FavoriteRepo::getInstance()->builder($filters)->first();
            if ($customerID != $favorite->customer_id) {
                throw new \Exception(front_trans('not_belongs_to_you'));
            }

            $favorite->delete();

            return json_success(front_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
