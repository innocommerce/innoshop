<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Models\Review;
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Throwable;

class ReviewController extends BaseController
{
    /**
     * @return mixed
     */
    public function index()
    {
        $filters = [
            'customer_id' => current_customer_id(),
        ];

        $data = [
            'reviews' => ReviewRepo::getInstance()->list($filters),
        ];

        return inno_view('account.reviews_index', $data);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
    {
        $productID = $request->get('product_id');
        $product   = Product::query()->findOrFail($productID);

        try {
            $data = $request->all();

            $data['customer_id'] = current_customer_id();

            ReviewRepo::getInstance()->create($data);

            return redirect($product->url)
                ->with('success', front_route('common.created_success'));
        } catch (Exception $e) {
            return redirect($product->url)
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Review  $review
     * @return JsonResponse
     */
    public function destroy(Review $review): JsonResponse
    {
        try {
            $review->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
