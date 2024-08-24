<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Review;
use InnoShop\Common\Repositories\ReviewRepo;

class ReviewController extends BaseController
{
    /**
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        $filters = [
            'customer_id' => token_customer_id(),
        ];

        return ReviewRepo::getInstance()->builder($filters)->paginate();
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $data   = $request->all();
        $review = ReviewRepo::getInstance()->create($data);

        return create_json_success($review);
    }

    /**
     * @param  Review  $review
     * @return JsonResponse
     */
    public function destroy(Review $review): JsonResponse
    {
        $review->delete();

        return delete_json_success();
    }
}
