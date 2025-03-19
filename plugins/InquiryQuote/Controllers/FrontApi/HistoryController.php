<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Controllers\FrontApi;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Services\CommentService;
use Plugin\InquiryQuote\Services\StateService;
use Throwable;

class HistoryController extends BaseController
{
    /**
     * @param  InquiryQuote  $quote
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(InquiryQuote $quote, Request $request): JsonResponse
    {
        try {
            $comment = CommentService::getInstance()->setQuote($quote)->getMessage($request->get('comment'));
            $history = StateService::getInstance($quote)->setComment($comment)->addHistory('', $quote->status);

            return create_json_success($history);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
