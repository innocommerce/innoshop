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
use InnoShop\Common\Repositories\ReviewRepo;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;

#[Group('Panel - Reviews')]
class ReviewController extends BaseController
{
    #[Endpoint('List reviews')]
    #[QueryParam('per_page', 'integer', required: false, example: 15)]
    public function index(Request $request): mixed
    {
        try {
            $filters = $request->all();
            $perPage = $request->get('per_page', 15);
            $reviews = ReviewRepo::getInstance()->builder($filters)->paginate($perPage);

            return read_json_success($reviews);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Get review detail')]
    #[UrlParam('id', 'integer', description: 'Review ID', example: 1)]
    public function show(int $id): mixed
    {
        try {
            $review = ReviewRepo::getInstance()->builder()->findOrFail($id);

            return read_json_success($review);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Update review')]
    #[UrlParam('id', 'integer', description: 'Review ID', example: 1)]
    public function update(Request $request, int $id): mixed
    {
        try {
            $review = ReviewRepo::getInstance()->builder()->findOrFail($id);
            ReviewRepo::getInstance()->update($review, $request->all());

            return update_json_success($review->fresh());
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    #[Endpoint('Delete review')]
    #[UrlParam('id', 'integer', description: 'Review ID', example: 1)]
    public function destroy(int $id): mixed
    {
        try {
            $review = ReviewRepo::getInstance()->builder()->findOrFail($id);
            ReviewRepo::getInstance()->destroy($review);

            return json_success('Review deleted successfully');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
