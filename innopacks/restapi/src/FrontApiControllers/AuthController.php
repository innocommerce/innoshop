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
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use InnoShop\Front\Requests\LoginRequest;
use InnoShop\Front\Requests\RegisterRequest;
use InnoShop\Front\Services\AccountService;
use Throwable;

class AuthController extends BaseController
{
    /**
     * @param  RegisterRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function register(RegisterRequest $request): mixed
    {
        try {
            $data = $request->only(['email', 'password', 'calling_code', 'telephone', 'code']);

            // Register by SMS code
            if (isset($data['calling_code']) && isset($data['telephone'])) {
                $customer = AccountService::getInstance()->registerBySms($data);
                auth('customer')->login($customer);
            } else {
                // Register by email and password
                $credentials = $request->only('email', 'password');
                $customer    = AccountService::getInstance()->register($credentials);
                auth('customer')->attempt($credentials);
            }

            $token = $customer->createToken('customer-token')->plainTextToken;

            return create_json_success(['token' => $token]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  LoginRequest  $request
     * @return mixed
     */
    public function login(LoginRequest $request): mixed
    {
        try {
            $data = $request->only(['email', 'password', 'calling_code', 'telephone', 'code']);

            // Login by SMS code
            if (isset($data['calling_code']) && isset($data['telephone'])) {
                $customer = AccountService::getInstance()->loginBySms($data);
                auth('customer')->login($customer);
            } else {
                // Login by email and password
                $credentials = $request->only('email', 'password');
                if (! auth('customer')->attempt($credentials)) {
                    throw ValidationException::withMessages(['email' => ['The provided credentials are incorrect.']]);
                }
            }

            $token = auth('customer')->user()->createToken('customer-token')->plainTextToken;

            return create_json_success(['token' => $token]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Send SMS verification code
     *
     * @param  Request  $request
     * @return mixed
     */
    public function sendSmsCode(Request $request): mixed
    {
        try {
            $request->validate([
                'calling_code' => 'required|string|max:10',
                'telephone'    => 'required|string|max:20',
                'type'         => 'required|in:register,login',
            ]);

            AccountService::getInstance()->sendSmsCode(
                $request->input('calling_code'),
                $request->input('telephone'),
                $request->input('type')
            );

            return create_json_success(['message' => 'SMS code sent successfully']);
        } catch (Exception $e) {
            $errorMessage = $e->getMessage();
            // Translate error message with specific error details (same format as backend test)
            $translatedMessage = __('common/sms.send_failed', ['message' => $errorMessage]);

            return json_fail($translatedMessage);
        }
    }
}
