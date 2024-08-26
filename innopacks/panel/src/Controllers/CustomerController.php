<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\Customer\GroupRepo;
use InnoShop\Common\Repositories\CustomerRepo;
use InnoShop\Common\Resources\AddressListItem;
use InnoShop\Panel\Requests\CustomerRequest;
use Throwable;

class CustomerController extends BaseController
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
            'customers' => CustomerRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::customers.index', $data);
    }

    /**
     * Customer creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Customer);
    }

    /**
     * @param  CustomerRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(CustomerRequest $request): RedirectResponse
    {
        try {
            $data     = $request->all();
            $customer = CustomerRepo::getInstance()->create($data);

            return redirect(panel_route('customers.index'))
                ->with('instance', $customer)
                ->with('success', panel_trans('common.saved_success'));
        } catch (Exception $e) {
            return redirect(panel_route('customers.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Customer  $customer
     * @return mixed
     * @throws Exception
     */
    public function edit(Customer $customer): mixed
    {
        return $this->form($customer);
    }

    /**
     * @param  $customer
     * @return mixed
     * @throws Exception
     */
    public function form($customer): mixed
    {
        $addresses = AddressListItem::collection($customer->addresses)->jsonSerialize();
        $data      = [
            'customer'  => $customer,
            'addresses' => $addresses,
            'groups'    => GroupRepo::getInstance()->getSimpleList(),
            'locales'   => locales()->toArray(),
        ];

        return inno_view('panel::customers.form', $data);
    }

    /**
     * @param  CustomerRequest  $request
     * @param  Customer  $customer
     * @return RedirectResponse
     */
    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        try {
            $data = $request->all();
            CustomerRepo::getInstance()->update($customer, $data);

            return redirect(panel_route('customers.index'))
                ->with('instance', $customer)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('customers.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Customer  $customer
     * @return RedirectResponse
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        try {
            CustomerRepo::getInstance()->destroy($customer);

            return redirect(panel_route('customers.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('customers.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
