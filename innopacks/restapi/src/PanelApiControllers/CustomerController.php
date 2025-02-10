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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Repositories\CustomerRepo;
use InnoShop\Common\Resources\CustomerSimple;
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
    public function names(Request $request): AnonymousResourceCollection
    {
        $customers = CustomerRepo::getInstance()->getListByCustomerIDs($request->get('ids'));

        return CustomerSimple::collection($customers);
    }

    /**
     * @param  CustomerRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CustomerRequest $request): JsonResponse
    {
        try {
            $data     = $request->all();
            $customer = CustomerRepo::getInstance()->create($data);

            return json_success(panel_trans('common.updated_success'), $customer);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  CustomerRequest  $request
     * @param  Customer  $customer
     * @return JsonResponse
     */
    public function update(CustomerRequest $request, Customer $customer): JsonResponse
    {
        try {
            $data = $request->all();
            CustomerRepo::getInstance()->update($customer, $data);

            return json_success(panel_trans('common.updated_success'), $customer);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Customer  $customer
     * @return JsonResponse
     */
    public function destroy(Customer $customer): JsonResponse
    {
        try {
            CustomerRepo::getInstance()->destroy($customer);

            return json_success(panel_trans('common.deleted_success'));
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
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $keyword  = $request->get('keyword');
        $catalogs = CustomerRepo::getInstance()->autocomplete($keyword);

        return CustomerSimple::collection($catalogs);
    }
}
