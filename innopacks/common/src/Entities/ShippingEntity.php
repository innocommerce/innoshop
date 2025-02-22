<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Entities;

use InnoShop\Common\Services\CheckoutService;
use Throwable;

class ShippingEntity
{
    private array $products;

    private float $subtotal;

    private float $amount;

    private float $weight;

    private array $origAddress;

    private array $destAddress;

    /**
     * @return $this
     */
    public static function getInstance(): static
    {
        return new static;
    }

    /**
     * @param  CheckoutService  $checkoutService
     * @return ShippingEntity
     * @throws Throwable
     */
    public function setCheckoutService(CheckoutService $checkoutService): static
    {
        $this->setProducts($checkoutService->getCartList());
        $this->setSubtotal($checkoutService->getSubTotal());
        $this->setAmount($checkoutService->getAmount());
        $this->setWeight($checkoutService->getCartWeight());
        $this->setOrigAddress([]);
        $this->setDestAddress($checkoutService->getShippingAddress());

        return $this;
    }

    /**
     * @param  array  $products
     * @return $this
     */
    public function setProducts(array $products): static
    {
        $this->products = $products;

        return $this;
    }

    /**
     * @param  float  $subtotal
     * @return $this
     */
    public function setSubtotal(float $subtotal): static
    {
        $this->subtotal = $subtotal;

        return $this;
    }

    /**
     * @param  float  $amount
     * @return $this
     */
    public function setAmount(float $amount): static
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * @param  float  $weight
     * @return $this
     */
    public function setWeight(float $weight): static
    {
        $this->weight = $weight;

        return $this;
    }

    /**
     * @param  array  $address
     * @return $this
     */
    public function setOrigAddress(array $address): static
    {
        $this->origAddress = $address;

        return $this;
    }

    /**
     * @param  array  $address
     * @return $this
     */
    public function setDestAddress(array $address): static
    {
        $this->destAddress = $address;

        return $this;
    }

    /**
     * @return array
     */
    public function getProducts(): array
    {
        return $this->products;
    }

    /**
     * @return float
     */
    public function getSubtotal(): float
    {
        return $this->subtotal;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @return float
     */
    public function getWeight(): float
    {
        return $this->weight;
    }

    /**
     * @return array
     */
    public function getOrigAddress(): array
    {
        return $this->origAddress;
    }

    /**
     * @return array
     */
    public function getDestAddress(): array
    {
        return $this->destAddress;
    }
}
