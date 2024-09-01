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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Review;
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\Common\Resources\ReviewListItem;
use Throwable;

class ReviewController extends BaseController
{
    /**
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $filters = [
            'customer_id' => token_customer_id(),
        ];

        $list = ReviewRepo::getInstance()->builder($filters)->paginate();

        return ReviewListItem::collection($list);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->all();

            $data['customer_id'] = token_customer_id();

            $review = ReviewRepo::getInstance()->create($data);

            return create_json_success(new ReviewListItem($review));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
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
