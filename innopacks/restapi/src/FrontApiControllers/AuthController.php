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
use Illuminate\Validation\ValidationException;
use InnoShop\Front\Requests\RegisterRequest;
use InnoShop\Front\Services\AccountService;
use Throwable;

class AuthController extends BaseController
{
    /**
     * @param  RegisterRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            $customer    = AccountService::getInstance()->register($credentials);
            auth('customer')->attempt($credentials);

            $token = $customer->createToken('customer-token')->plainTextToken;

            return create_json_success(['token' => $token]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');
            if (! auth('customer')->attempt($credentials)) {
                throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
            }
            $token = auth('customer')->user()->createToken('customer-token')->plainTextToken;

            return create_json_success(['token' => $token]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
