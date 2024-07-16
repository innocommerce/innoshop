<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use InnoShop\Common\Models\Checkout;
use InnoShop\Common\Repositories\AddressRepo;
use InnoShop\Common\Repositories\CheckoutRepo;
use InnoShop\Common\Repositories\Order\FeeRepo;
use InnoShop\Common\Repositories\Order\HistoryRepo;
use InnoShop\Common\Repositories\Order\ItemRepo;
use InnoShop\Common\Repositories\OrderRepo;
use InnoShop\Common\Resources\AddressListItem;
use InnoShop\Common\Resources\CheckoutSimple;
use InnoShop\Common\Services\Checkout\BillingService;
use InnoShop\Common\Services\Checkout\FeeService;
use InnoShop\Common\Services\Checkout\ShippingService;
use InnoShop\Common\Services\Fee\Subtotal;
use Throwable;

class CheckoutService extends BaseService
{
    private int $customerID;

    private string $guestID;

    private array $cartList = [];

    private array $addressList = [];

    private array $feeList = [];

    private ?Checkout $checkout = null;

    private array $checkoutData = [];

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @throws Throwable
     */
    public function __construct(int $customerID = 0, string $guestID = '')
    {
        if ($customerID) {
            $this->customerID = $customerID;
        } else {
            $this->customerID = current_customer_id();
        }

        if ($guestID) {
            $this->guestID = $guestID;
        } else {
            $this->guestID = current_guest_id();
        }

        $this->clearGuestAddresses();
    }

    /**
     * @param  int  $customerID
     * @param  string  $guestID
     * @return static
     * @throws Throwable
     */
    public static function getInstance(int $customerID = 0, string $guestID = ''): static
    {
        return new self($customerID, $guestID);
    }

    /**
     * Get current cart item list.
     *
     * @return array
     */
    public function getCartList(): array
    {
        if ($this->cartList) {
            return $this->cartList;
        }

        return $this->cartList = CartService::getInstance($this->customerID, $this->guestID)->getCartList();
    }

    /**
     * @return bool
     */
    public function checkIsVirtual(): bool
    {
        $cartList = $this->getCartList();
        foreach ($cartList as $product) {
            if (! $product['is_virtual']) {
                return false;
            }
        }

        return true;
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
            'guest_id'    => $this->guestID,
        ];
        $addresses = AddressRepo::getInstance()->builder($filters)->get();

        return $this->addressList = (AddressListItem::collection($addresses))->jsonSerialize();
    }

    /**
     * @return float
     */
    public function getSubTotal(): float
    {
        return (new Subtotal($this))->getSubtotal();
    }

    /**
     * Get fee list.
     *
     * @return array
     * @throws Exception
     */
    public function getFeeList(): array
    {
        if ($this->feeList) {
            return $this->feeList;
        }

        FeeService::getInstance($this)->calculate();
        if (empty($this->feeList)) {
            throw new Exception('Empty checkout fee list !');
        }

        return $this->feeList;
    }

    /**
     * @param  array  $fee
     * @return $this
     */
    public function addFeeList(array $fee): static
    {
        $this->feeList[] = $fee;

        return $this;
    }

    /**
     * @return float
     * @throws Exception
     */
    public function getTotal(): float
    {
        $feeList = $this->getFeeList();

        return round(collect($feeList)->sum('total'), 2);
    }

    /**
     * @return array
     * @throws Throwable
     */
    public function getCheckoutData(): array
    {
        if ($this->checkoutData) {
            return $this->checkoutData;
        }

        $checkout = $this->getCheckout();

        return $this->checkoutData = (new CheckoutSimple($checkout))->jsonSerialize();
    }

    /**
     * @return Checkout|null
     * @throws Throwable
     */
    public function getCheckout(): ?Checkout
    {
        if ($this->checkout) {
            return $this->checkout;
        }

        $data = [
            'customer_id' => $this->customerID,
            'guest_id'    => $this->guestID,
        ];
        $checkout = CheckoutRepo::getInstance()->builder($data)->first();

        if (empty($checkout)) {
            $checkout = $this->createCheckout($data);
        }

        return $this->checkout = $checkout;
    }

    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function createCheckout($data): mixed
    {
        $addressList    = $this->getAddressList();
        $defaultAddress = $addressList[0] ?? null;

        $shippingMethods = ShippingService::getInstance($this)->getMethods();
        $billingMethods  = BillingService::getInstance($this)->getMethods();

        $data['shipping_address_id']  = $defaultAddress['id']       ?? 0;
        $data['shipping_method_code'] = $shippingMethods[0]['code'] ?? '';
        $data['billing_address_id']   = $defaultAddress['id']       ?? 0;
        $data['billing_method_code']  = $billingMethods[0]['code']  ?? '';

        return CheckoutRepo::getInstance()->create($data);
    }

    /**
     * Get checkout result.
     *
     * @return array
     * @throws Exception|Throwable
     */
    public function getCheckoutResult(): array
    {
        return [
            'cart_list'        => $this->getCartList(),
            'address_list'     => $this->getAddressList(),
            'shipping_methods' => ShippingService::getInstance($this)->getMethods(),
            'billing_methods'  => BillingService::getInstance($this)->getMethods(),
            'checkout'         => $this->getCheckoutData(),
            'fee_list'         => $this->getFeeList(),
            'total'            => $this->getTotal(),
            'is_virtual'       => $this->checkIsVirtual(),
        ];
    }

    /**
     * @return void
     */
    private function clearGuestAddresses(): void
    {
        AddressRepo::getInstance()->clearExpiredAddresses();
    }

    /**
     * @param  $values
     * @return mixed
     * @throws Throwable
     */
    public function updateValues($values): mixed
    {
        $checkout = $this->getCheckout();

        return CheckoutRepo::getInstance()->update($checkout, $values);
    }

    /**
     * Confirm checkout and place order.
     *
     * @return mixed
     * @throws Exception|Throwable
     */
    public function confirm(): mixed
    {
        DB::beginTransaction();

        try {
            $checkoutData          = (new CheckoutSimple($this->checkout))->jsonSerialize();
            $checkoutData['total'] = $this->getTotal();

            $order = OrderRepo::getInstance()->create($checkoutData);

            ItemRepo::getInstance()->createItems($order, $this->cartList);
            FeeRepo::getInstance()->createItems($order, $this->feeList);
            HistoryRepo::getInstance()->initItem($order);

            DB::commit();

            $this->checkout->delete();
            CartService::getInstance()->getCartBuilder()->delete();

            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
