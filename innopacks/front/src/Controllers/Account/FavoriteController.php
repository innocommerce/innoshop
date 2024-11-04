<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\Customer\FavoriteRepo;

class FavoriteController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        $filters = [
            'customer_id' => current_customer_id(),
        ];
        $favorites = FavoriteRepo::getInstance()->list($filters);

        $data = [
            'favorites' => $favorites,
        ];

        return inno_view('account.favorites', $data);
    }

    /**
     * Add to favorite list.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $data = [
                'customer_id' => current_customer_id(),
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
     * @return mixed
     */
    public function cancel(Request $request): mixed
    {
        try {
            $filters = [
                'customer_id' => current_customer_id(),
                'product_id'  => $request->get('product_id'),
            ];

            $favorite = FavoriteRepo::getInstance()->builder($filters)->first();
            if (current_customer_id() != $favorite->customer_id) {
                throw new \Exception(front_trans('not_belongs_to_you'));
            }

            $favorite->delete();

            return json_success(front_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
