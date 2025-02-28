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
use InnoShop\RestAPI\FrontApiControllers\BaseController;
use Plugin\InquiryQuote\Models\InquiryQuoteItem;
use Plugin\InquiryQuote\Repositories\InquiryRepo;
use Plugin\InquiryQuote\Resources\QuoteSimple;
use Plugin\InquiryQuote\Services\CommentService;
use Plugin\InquiryQuote\Services\SplittingService;
use Plugin\InquiryQuote\Services\StateService;
use Throwable;

class InquiryController extends BaseController
{
    /**
     * @param  Request  $request
     * @param  InquiryQuoteItem  $inquiry
     * @return JsonResponse
     * @throws Throwable
     */
    public function update(Request $request, InquiryQuoteItem $inquiry): JsonResponse
    {
        try {
            $data = $request->all();

            $inquiry = InquiryRepo::getInstance()->update($inquiry, $data);
            SplittingService::getInstance($inquiry->quote)->split();

            $comment = CommentService::getInstance()->setInquiry($inquiry)->getMessage($data);
            StateService::getInstance($inquiry->quote)->setComment($comment)->changeStatus(StateService::ADMIN_UPDATED);

            return update_json_success(new QuoteSimple($inquiry));

        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
