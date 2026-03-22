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
use InnoShop\Common\Models\Review;
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\Common\Resources\ReviewListItem;
use Knuckles\Scribe\Attributes\Authenticated;
use Knuckles\Scribe\Attributes\BodyParam;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Front - Reviews')]
#[Authenticated]
class ReviewController extends BaseController
{
    /**
     * @return AnonymousResourceCollection
     */
    #[Endpoint('List my reviews')]
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
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create review')]
    #[BodyParam('product_id', type: 'integer', required: true)]
    #[BodyParam('order_item_id', type: 'integer', required: true)]
    #[BodyParam('rating', type: 'integer', required: true, example: 5)]
    #[BodyParam('content', type: 'string', required: false)]
    public function store(Request $request): mixed
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
     * @return mixed
     */
    #[Endpoint('Delete review')]
    #[UrlParam('review', type: 'integer', description: 'Review ID')]
    public function destroy(Review $review): mixed
    {
        if ($review->customer_id !== token_customer_id()) {
            return json_fail('Unauthorized', null, 403);
        }

        $review->delete();

        return delete_json_success();
    }
}
