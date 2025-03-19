<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Controllers\Front;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Front\Controllers\BaseController;
use Plugin\InquiryQuote\Models\InquiryQuote;
use Plugin\InquiryQuote\Repositories\QuoteRepo;
use Plugin\InquiryQuote\Services\PlaceOrderService;
use Plugin\InquiryQuote\Services\QuoteService;
use Throwable;

class QuoteController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function current(): mixed
    {
        $customerID = current_customer_id();
        $result     = QuoteService::getInstance($customerID)->handleResponse();

        return view('InquiryQuote::front.quote.index', $result);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $filters['customer_id'] = current_customer_id();

        $data = [
            'quotes' => QuoteRepo::getInstance()->list($filters),
        ];

        return view('InquiryQuote::front.account.quote_index', $data);
    }

    /**
     * @param  InquiryQuote  $quote
     * @return mixed
     * @throws Exception
     */
    public function show(InquiryQuote $quote): mixed
    {
        $data = QuoteRepo::getInstance()->getDetails($quote);

        $data['quote'] = $quote;

        return view('InquiryQuote::front.account.quote_show', $data);
    }

    /**
     * @param  string  $number
     * @return mixed
     * @throws Exception
     */
    public function numberShow(string $number): mixed
    {
        $quote = QuoteRepo::getInstance()->builder(['number' => $number])->firstOrFail();
        $data  = QuoteRepo::getInstance()->getDetails($quote);

        $data['quote'] = $quote;

        return view('InquiryQuote::front.account.quote_show', $data);
    }

    /**
     * @param  string  $number
     * @return void
     */
    public function checkout(string $number) {}

    /**
     * @param  string  $number
     * @return mixed
     * @throws Throwable
     */
    public function confirm(string $number): mixed
    {
        try {
            $quote = QuoteRepo::getInstance()->builder(['number' => $number])->firstOrFail();
            $order = PlaceOrderService::getInstance($quote)->confirm();

            return redirect(front_route('orders.pay', ['number' => $order->number]));
        } catch (\Exception $e) {
            return redirect(account_route('quotes.index'))
                ->with(['error' => $e->getMessage()]);
        }

    }
}
