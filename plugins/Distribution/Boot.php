<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Distribution;

use InnoShop\Common\Models\Customer;
use InnoShop\Common\Services\StateMachineService;
use Plugin\Coupon\Models\Order;
use Plugin\Distribution\Models\Commission;
use Plugin\Distribution\Services\ReferralService;
use Throwable;

class Boot
{
    /**
     * @return void
     * @throws Throwable
     */
    public function init(): void
    {
        $this->generateCustomerReferralCode();
        $this->updateCustomerReferrer();
        $this->updateOrderReferrer();

        $this->addAccountSidebarMenu();
        $this->addPanelCustomerTabContent();

        $this->updateCommissionAfterComplete();
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function generateCustomerReferralCode(): void
    {
        if (request()->path() == 'panel/customers') {
            ReferralService::getInstance()->saveReferralCode();
        }
    }

    /**
     * @return void
     */
    public function updateCustomerReferrer(): void
    {
        listen_hook_action('front.service.account.register', function ($customer) {
            $refCode = session('ref_code');
            if (empty($refCode)) {
                return;
            }

            $referrer = Customer::query()->where('referral_code', $refCode)->first();
            if (empty($referrer)) {
                return;
            }

            $customer->referral_code = ReferralService::generateReferralCode();
            $customer->referrer_id   = $referrer->id;
            $customer->save();
        });
    }

    /**
     * @return void
     * @throws Throwable
     */
    private function updateOrderReferrer(): void
    {
        listen_hook_action('service.checkout.confirm.after', function ($data) {
            ReferralService::getInstance()->updateReferrer($data['order']);
        });
    }

    /**
     * @return void
     */
    private function addAccountSidebarMenu(): void
    {
        listen_blade_insert('front.account.sidebar.reviews.after', function ($data) {
            $routes = [
                'front.account.distributions.index',
                'front.account.distributions.members',
                'front.account.distributions.commissions',
                'front.account.distributions.orders',
            ];

            $data['is_active'] = equal_route_name($routes);

            return view('Distribution::account.sidebar_menu', $data);
        });
    }

    /**
     * @return void
     */
    private function addPanelCustomerTabContent(): void
    {
        listen_blade_insert('panel.customer.edit.tab.nav.bottom', function ($data) {
            return view('Distribution::panel.customer_tab');
        });

        listen_blade_insert('panel.customer.edit.tab.pane.bottom', function ($data) {
            $customer = $data['customer'];

            $customerID = $customer->id;

            $data['report']      = ReferralService::getInstance()->getReport($customerID);
            $data['members']     = Customer::query()->where('referrer_id', $customerID)->get();
            $data['orders']      = Order::query()->where('referrer_id', $customerID)->get();
            $data['commissions'] = Commission::query()->where('referrer_id', $customerID)->get();

            return view('Distribution::panel.customer_tab_content', $data);
        });
    }

    /**
     * @return void
     */
    private function updateCommissionAfterComplete(): void
    {
        listen_hook_filter('service.state_machine.machines', function ($data) {
            $data['machines'][StateMachineService::PAID][StateMachineService::COMPLETED][] = function () use ($data) {
                ReferralService::completeOrder($data['order']);
            };

            $data['machines'][StateMachineService::SHIPPED][StateMachineService::COMPLETED][] = function () use ($data) {
                ReferralService::completeOrder($data['order']);
            };

            return $data;
        });
    }
}
