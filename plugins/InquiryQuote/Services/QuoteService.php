<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use InnoShop\Common\Repositories\AddressRepo;
use InnoShop\Common\Resources\AddressListItem;
use Plugin\InquiryQuote\Repositories\InquiryRepo;
use Plugin\InquiryQuote\Repositories\QuoteRepo;
use Plugin\InquiryQuote\Resources\InquiryListItem;
use Plugin\InquiryQuote\Services\Quote\ShippingService;

class QuoteService
{
    private int $customerID;

    private array $addressList = [];

    private static ?array $inquiryList = null;

    /**
     * @param  int  $customerID
     * @throws Exception
     */
    public function __construct(int $customerID = 0)
    {
        if ($customerID) {
            $this->customerID = $customerID;
        } else {
            $this->customerID = current_customer_id();
        }

        if (empty($this->customerID)) {
            throw new Exception('Empty customer ID!');
        }
    }

    /**
     * @param  int  $customerID
     * @return static
     * @throws Exception
     */
    public static function getInstance(int $customerID = 0): static
    {
        return new static($customerID);
    }

    /**
     * @param  array  $filters
     * @return Builder
     */
    public function getInquiryBuilder(array $filters = []): Builder
    {
        $filters = $this->mergeAuthId($filters);

        return InquiryRepo::getInstance()->builder($filters);
    }

    /**
     * @param  array  $filters
     * @return Collection
     */
    public function getInquiryItems(array $filters = []): Collection
    {
        $cartItems = $this->getInquiryBuilder($filters)->get();

        return $cartItems->filter(function ($item) {
            if (empty($item->product) || empty($item->productSku)) {
                $item->delete();
            }

            return $item->product && $item->productSku;
        });
    }

    /**
     * @param  array  $filters
     * @return array
     */
    public function getInquiryList(array $filters = []): array
    {
        if (self::$inquiryList !== null) {
            return self::$inquiryList;
        }

        $cartItems = $this->getInquiryItems($filters);

        return self::$inquiryList = InquiryListItem::collection($cartItems)->jsonSerialize();
    }

    /**
     * @return int
     */
    public function getSubTotal(): int
    {
        $inquiryList = $this->getInquiryList();

        return collect($inquiryList)->sum('inquiry_subtotal');
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->getSubTotal();
    }

    /**
     * @return int
     */
    public function getCartWeight(): int
    {
        $inquiryList = $this->getInquiryList();

        return collect($inquiryList)->sum('weight');
    }

    /**
     * @return mixed
     */
    public function getDefaultAddress(): array
    {
        $addressList = $this->getAddressList();
        if (empty($addressList)) {
            return [];
        }

        $defaultAddress = collect($addressList)->where('default', 1)->first();

        return $defaultAddress ?: $addressList[0];
    }

    /**
     * @return array
     */
    public function getShippingAddress(): array
    {
        $quote = QuoteRepo::getInstance()->getLatestByCustomerID($this->customerID);

        $address = $quote->shippingAddress ?? null;
        if (empty($address)) {
            return $this->getDefaultAddress();
        }

        return $address->toArray();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function handleResponse(): array
    {
        $quote  = QuoteRepo::getInstance()->getLatestByCustomerID($this->customerID);
        $result = QuoteRepo::getInstance()->getDetails($quote);

        $result['address_list']     = $this->getAddressList();
        $result['shipping_methods'] = ShippingService::getInstance()->setQuoteService($this)->getMethods();

        return fire_hook_filter('services.quote.response', $result);
    }

    /**
     * Get current address list.
     *
     * @return array
     */
    public function getAddressList(): array
    {
        if ($this->addressList) {
            return $this->addressList;
        }

        $filters = [
            'customer_id' => $this->customerID,
        ];
        $addresses = AddressRepo::getInstance()->builder($filters)->get();

        return $this->addressList = (AddressListItem::collection($addresses))->jsonSerialize();
    }

    /**
     * @param  array  $data
     * @return array
     */
    protected function mergeAuthId(array $data): array
    {
        $data['customer_id'] = $this->customerID;

        return $data;
    }
}
