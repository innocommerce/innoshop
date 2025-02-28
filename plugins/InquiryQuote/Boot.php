<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InquiryQuote;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\InquiryQuote\Repositories\QuoteRepo;
use Spatie\Permission\Models\Role;

class Boot extends BaseBoot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->addPanelMenu();
        $this->addPanelCustomerSalesman();
        $this->saveCustomerSalesman();

        $this->addSellerMenu();

        $this->addHeaderButton();
        $this->addProductDetailButton();
        $this->addAccountSidebarMenu();

        $this->setSalesmanAfterRegister();
        $this->addCustomerBuilderSalesman();
    }

    /**
     * @return void
     */
    private function addPanelMenu(): void
    {
        listen_hook_filter('panel.component.sidebar.order.routes', function ($data) {
            $data[] = [
                'route' => 'quotes.index',
                'title' => '询盘议价',
            ];

            return $data;
        });
    }

    /**
     * @return void
     */
    private function addPanelCustomerSalesman(): void
    {
        listen_blade_insert('panel.customer.form.group.after', function ($data) {
            $salesRoleID      = plugin_setting('inquiry_quote', 'salesman_role_id');
            $salesRole        = Role::query()->find($salesRoleID);
            $data['salesmen'] = $salesRole->users->toArray();

            return view('InquiryQuote::panel.customer.salesman', $data);
        });
    }

    /**
     * @return void
     */
    private function saveCustomerSalesman(): void
    {
        listen_hook_filter('repo.customer.update', function ($customer) {
            $customer->admin_id = (int) request('admin_id');
            $customer->save();
        });
    }

    /**
     * @return void
     */
    private function addSellerMenu(): void
    {
        listen_hook_filter('seller.component.sidebar.order.routes', function ($data) {
            $data[] = [
                'route' => 'quotes.index',
                'title' => '询盘议价',
            ];

            return $data;
        });
    }

    /**
     * @return void
     */
    private function addHeaderButton(): void
    {
        listen_blade_insert('layouts.header.cart.after', function ($data) {
            $data['quote_quantity'] = 0;

            $customerID = current_customer_id();
            if ($customerID) {
                $currentQuote = QuoteRepo::getInstance()->getLatestByCustomerID($customerID);
                if ($currentQuote) {
                    $data['quote_quantity'] = $currentQuote->items->sum('quantity');
                }
            }

            return view('InquiryQuote::front.header_quote_button', $data);
        });
    }

    /**
     * @return void
     */
    private function addProductDetailButton(): void
    {
        if (plugin_setting('inquiry_quote', 'based_seller') || plugin_setting('inquiry_quote', 'based_salesman')) {
            listen_blade_insert('product.detail.cart.after', function ($data) {
                return view('InquiryQuote::front.product.detail_quote_button', $data);
            });
        }
    }

    /**
     * @return void
     */
    private function addAccountSidebarMenu(): void
    {
        listen_blade_insert('front.account.sidebar.reviews.after', function ($data) {
            return view('InquiryQuote::front.account.my_quotes_menu');
        });
    }

    /**
     * @return void
     * @throws Exception
     */
    private function setSalesmanAfterRegister(): void
    {
        listen_hook_filter('repo.customer.create', function ($customer) {
            $salesmanID = plugin_setting('inquiry_quote', 'salesman_admin_id');
            if (empty($salesmanID)) {
                throw new Exception('Empty salesman admin ID');
            }

            $customer->admin_id = $salesmanID;
            $customer->save();

            return $customer;
        });
    }

    /**
     * @return void
     */
    private function addCustomerBuilderSalesman(): void
    {
        listen_hook_filter('repo.customer.builder', function ($builder) {
            if (! is_admin()) {
                return $builder;
            }

            $salesRoleID = plugin_setting('inquiry_quote', 'salesman_role_id');
            $salesRole   = Role::query()->find($salesRoleID);
            if (empty($salesRole)) {
                return $builder;
            }

            $currentAdmin = current_admin();
            if ($currentAdmin && $currentAdmin->hasRole($salesRole)) {
                $builder->where('admin_id', $currentAdmin->id);
            }

            return $builder;
        });
    }
}
