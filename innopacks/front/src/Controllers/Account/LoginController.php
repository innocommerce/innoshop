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
use InnoShop\Common\Services\CartService;
use InnoShop\Front\Requests\LoginRequest;
use InnoShop\Front\Services\AccountService;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class LoginController extends Controller
{
    use SendSmsCodeTrait;

    /**
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        if (current_customer()) {
            return redirect(front_route('account.index'));
        }

        $authMethod = system_setting('auth_method', 'both');

        return inno_view('account.login', compact('authMethod'));
    }

    /**
     * Login request
     *
     * @param  LoginRequest  $request
     * @return mixed
     */
    public function store(LoginRequest $request): mixed
    {
        try {
            $authMethod  = system_setting('auth_method', 'both');
            $oldGuestId  = current_guest_id();
            $redirectUri = session('front_redirect_uri');
            $data        = $request->only(['email', 'password', 'calling_code', 'telephone', 'code']);

            // Validate auth method
            if ($authMethod === 'email_only' && (! isset($data['email']) || empty($data['email']))) {
                throw new NotAcceptableHttpException(front_trans('login.email_required'));
            }

            if ($authMethod === 'phone_only' && (! isset($data['calling_code']) || ! isset($data['telephone']))) {
                throw new NotAcceptableHttpException(front_trans('login.phone_required'));
            }

            // Login by SMS code
            if (isset($data['calling_code']) && isset($data['telephone'])) {
                // Clean and format phone data
                $data['calling_code'] = trim($data['calling_code'] ?? '');
                $data['telephone']    = trim($data['telephone'] ?? '');

                // Remove any non-digit characters from telephone
                $data['telephone'] = preg_replace('/[^0-9]/', '', $data['telephone']);

                // Ensure calling_code has + prefix if not empty
                if (! empty($data['calling_code']) && ! str_starts_with($data['calling_code'], '+')) {
                    $data['calling_code'] = '+'.ltrim($data['calling_code'], '+');
                }

                $customer = AccountService::getInstance()->loginBySms($data);
                auth('customer')->login($customer);
            } else {
                // Login by email and password
                if (! auth('customer')->attempt($request->only('email', 'password'))) {
                    throw new NotAcceptableHttpException(front_trans('login.account_or_password_error'));
                }
            }

            $customer = current_customer();
            if (empty($customer)) {
                throw new NotFoundHttpException(front_trans('login.empty_customer'));
            }

            if (! $customer->active) {
                auth('customer')->logout();
                throw new Exception(front_trans('login.inactive_customer'));
            }

            CartService::getInstance(current_customer_id())->mergeCart($oldGuestId);
            session()->forget('front_redirect_uri');
            $data = ['redirect_uri' => $redirectUri];

            return json_success(front_trans('login.login_success'), $data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Send SMS verification code for login
     *
     * @return mixed
     */
    public function sendSmsCode(): mixed
    {
        return $this->sendSmsCodeInternal('login');
    }
}
