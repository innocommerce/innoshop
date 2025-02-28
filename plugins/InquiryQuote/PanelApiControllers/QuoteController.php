<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\PanelApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Repositories\QuoteRepo;
use Plugin\InquiryQuote\Resources\QuoteSimple;
use Plugin\InquiryQuote\Services\CommentService;
use Plugin\InquiryQuote\Services\SplittingService;
use Plugin\InquiryQuote\Services\StateService;
use Throwable;

class QuoteController extends BaseController
{
    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->all();

        return QuoteSimple::collection(QuoteRepo::getInstance()->list($filters));
    }

    /**
     * @param  InquiryQuote  $quote
     * @return JsonResponse
     * @throws Exception
     */
    public function show(InquiryQuote $quote): JsonResponse
    {
        $result = QuoteRepo::getInstance()->getDetails($quote);

        return read_json_success($result);
    }

    /**
     * @param  Request  $request
     * @param  InquiryQuote  $quote
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request, InquiryQuote $quote): JsonResponse
    {
        try {
            $data = $request->all();

            $quote = QuoteRepo::getInstance()->update($quote, $data);

            $comment = CommentService::getInstance()->setQuote($quote)->getMessage($data);
            StateService::getInstance($quote)->setComment($comment)->changeStatus(StateService::ADMIN_UPDATED);

            $result = QuoteRepo::getInstance()->getDetails($quote);

            return update_json_success($result);

        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  InquiryQuote  $quote
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateFees(Request $request, InquiryQuote $quote): JsonResponse
    {
        try {
            $fees = $request->all();
            QuoteRepo::getInstance()->updateFees($quote, $fees);
            SplittingService::getInstance($quote)->split();

            $comment = CommentService::getInstance()->setQuote($quote)->getMessage($fees);
            StateService::getInstance($quote)->setComment($comment)->changeStatus(StateService::ADMIN_UPDATED);

            $result = QuoteRepo::getInstance()->getDetails($quote);

            return update_json_success($result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  InquiryQuote  $quote
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateStatus(Request $request, InquiryQuote $quote): JsonResponse
    {
        try {
            $status = $request->get('status');
            $quote  = QuoteRepo::getInstance()->update($quote, ['status' => $status]);
            $result = QuoteRepo::getInstance()->getDetails($quote);

            return update_json_success($result);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  InquiryQuote  $quote
     * @return JsonResponse
     */
    public function destroy(InquiryQuote $quote): JsonResponse
    {
        try {
            $quote->items()->delete();
            $quote->fees()->delete();
            $quote->delete();

            return delete_json_success();

        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
