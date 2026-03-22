<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\CustomerRepo;
use InnoShop\Common\Resources\CustomerSimple;
use InnoShop\Panel\Requests\CustomerRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Panel - Customers')]
class CustomerController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List customers')]
    #[QueryParam('customer_ids', 'string', required: false, description: 'Comma-separated customer IDs')]
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        if (isset($filters['customer_ids'])) {
            $customerIds             = explode(',', $filters['customer_ids']);
            $filters['customer_ids'] = $customerIds;
        }

        $catalogs = CustomerRepo::getInstance()->builder($filters)->limit(10)->get();

        return CustomerSimple::collection($catalogs);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get customers by IDs')]
    #[QueryParam('ids', 'string', required: true)]
    public function names(Request $request): AnonymousResourceCollection
    {
        $customers = CustomerRepo::getInstance()->getListByCustomerIDs($request->get('ids'));

        return CustomerSimple::collection($customers);
    }

    /**
     * @param  CustomerRequest  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create customer')]
    public function store(CustomerRequest $request): mixed
    {
        try {
            $data     = $request->all();
            $customer = CustomerRepo::getInstance()->create($data);

            return json_success(common_trans('base.updated_success'), $customer);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CustomerRequest  $request
     * @param  Customer  $customer
     * @return mixed
     */
    #[Endpoint('Update customer')]
    #[UrlParam('customer', 'integer', description: 'Customer ID')]
    public function update(CustomerRequest $request, Customer $customer): mixed
    {
        try {
            $data = $request->all();
            CustomerRepo::getInstance()->update($customer, $data);

            return json_success(common_trans('base.updated_success'), $customer);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update a customer.
     * PATCH /api/panel/customers/{customer}
     *
     * @param  CustomerRequest  $request
     * @param  Customer  $customer
     * @return mixed
     */
    #[Endpoint('Partial update customer')]
    #[UrlParam('customer', 'integer', description: 'Customer ID')]
    public function patch(CustomerRequest $request, Customer $customer): mixed
    {
        try {
            $data = $request->validated();
            CustomerRepo::getInstance()->patch($customer, $data);

            return update_json_success($customer);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Customer  $customer
     * @return mixed
     */
    #[Endpoint('Delete customer')]
    #[UrlParam('customer', 'integer', description: 'Customer ID')]
    public function destroy(Customer $customer): mixed
    {
        try {
            CustomerRepo::getInstance()->destroy($customer);

            return json_success(common_trans('base.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/customers/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Autocomplete customers')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $keyword  = $request->get('keyword');
        $catalogs = CustomerRepo::getInstance()->autocomplete($keyword);

        return CustomerSimple::collection($catalogs);
    }
}
