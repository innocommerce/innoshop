<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\PanelControllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Repositories\QuoteRepo;
use Plugin\InquiryQuote\Services\SplittingService;
use Plugin\InquiryQuote\Services\StateService;
use Throwable;

class QuoteController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'criteria' => OrderRepo::getCriteria(),
            'quotes'   => QuoteRepo::getInstance()->list($filters),
        ];

        return view('InquiryQuote::panel.quote_index', $data);
    }

    /**
     * @param  InquiryQuote  $quote
     * @return mixed
     * @throws Exception|Throwable
     */
    public function edit(InquiryQuote $quote): mixed
    {
        $data = QuoteRepo::getInstance()->getDetails($quote);

        $data['quote']         = $quote;
        $data['next_statuses'] = StateService::getInstance($quote)->nextBackendStatuses();

        $data['excluded'] = [
            'admin_updated',
            'customer_updated',
            'customer_closed',
        ];

        return view('InquiryQuote::panel.quote_edit', $data);
    }

    /**
     * @param  InquiryQuote  $quote
     * @return void
     * @throws Throwable
     */
    public function split(InquiryQuote $quote): void
    {
        SplittingService::getInstance($quote)->split();
    }
}
