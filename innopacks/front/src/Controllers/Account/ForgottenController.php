<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use InnoShop\Front\Requests\ForgottenRequest;
use InnoShop\Front\Requests\VerifyCodeRequest;
use InnoShop\Front\Services\AccountService;

class ForgottenController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return view('account.forgotten');
    }

    /**
     * Receive the email address, generate a verification code, and send it to the email address.
     *
     * @param  VerifyCodeRequest  $request
     * @return JsonResponse
     */
    public function sendVerifyCode(VerifyCodeRequest $request): JsonResponse
    {
        try {
            $email = $request->get('email');
            AccountService::getInstance()->sendVerifyCode($email);

            return json_success(front_trans('forgotten.verification_code_sent'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Receive the verification code and new password, confirm the password, check if the verification code is correct,
     * and if the password matches the confirmed password, then change the password.
     *
     * @param  ForgottenRequest  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function changePassword(ForgottenRequest $request): JsonResponse
    {
        try {
            $code     = $request->get('code');
            $email    = $request->get('email');
            $password = $request->get('password');

            AccountService::getInstance()->verifyUpdatePassword($code, $email, $password);

            return json_success(front_trans('forgotten.password_updated'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
