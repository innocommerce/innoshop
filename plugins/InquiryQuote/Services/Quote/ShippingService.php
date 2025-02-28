<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote\Services\Quote;

use Exception;
use InnoShop\Common\Entities\ShippingEntity;
use Plugin\InquiryQuote\Services\QuoteService;

class ShippingService
{
    public static ?array $shippingMethods = null;

    private ?QuoteService $quoteService;

    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  QuoteService  $quoteService
     * @return ShippingService
     */
    public function setQuoteService(QuoteService $quoteService): static
    {
        $this->quoteService = $quoteService;

        return $this;
    }

    /**
     * @return ShippingEntity
     */
    private function getShippingEntity(): ShippingEntity
    {
        $shippingEntity = ShippingEntity::getInstance();
        $shippingEntity->setProducts($this->quoteService->getInquiryList());
        $shippingEntity->setSubtotal($this->quoteService->getSubTotal());
        $shippingEntity->setAmount($this->quoteService->getTotal());
        $shippingEntity->setWeight($this->quoteService->getCartWeight());
        $shippingEntity->setOrigAddress([]);
        $shippingEntity->setDestAddress($this->quoteService->getShippingAddress());

        return $shippingEntity;
    }

    /**
     * @throws Exception
     */
    public function getMethods(): array
    {
        $shippingEntity = $this->getShippingEntity();

        return \InnoShop\Common\Services\Checkout\ShippingService::getInstance()
            ->setShippingEntity($shippingEntity)
            ->getMethods();
    }
}
