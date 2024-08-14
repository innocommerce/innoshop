<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\RestAPI\Libraries\MiniApp\Auth;
use Symfony\Contracts\HttpClient\Exception as HttpClientException;
use Throwable;

class MiniappController extends BaseController
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws HttpClientException\DecodingExceptionInterface
     * @throws HttpClientException\RedirectionExceptionInterface
     * @throws HttpClientException\ServerExceptionInterface
     * @throws HttpClientException\TransportExceptionInterface
     * @throws Throwable
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $code = $request->get('code');
            if (empty($code)) {
                throw new Exception('Empty MiniApp Code');
            }

            $miniAppAuth = Auth::getInstance($code);
            $customer    = $miniAppAuth->findOrCreateCustomerByCode();

            $token = $customer->createToken('customer-token')->plainTextToken;

            return create_json_success(['token' => $token]);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
