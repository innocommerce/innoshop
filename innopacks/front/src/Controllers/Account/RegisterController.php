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
use InnoShop\Front\Requests\RegisterRequest;
use InnoShop\Front\Services\AccountService;
use Throwable;

class RegisterController extends Controller
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

        $authMethod = auth_method();

        return inno_view('account.register', compact('authMethod'));
    }

    /**
     * @param  RegisterRequest  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(RegisterRequest $request): mixed
    {
        try {
            $oldGuestId = current_guest_id();
            $data       = $request->only(['email', 'password', 'calling_code', 'telephone', 'code']);

            // Register by SMS code
            if (isset($data['calling_code']) && isset($data['telephone'])) {
                // Clean and format phone data
                $data['calling_code'] = trim($data['calling_code'] ?? '');
                $data['telephone']    = trim($data['telephone'] ?? '');

                // Remove any non-digit characters from telephone (except if it's already clean)
                $data['telephone'] = preg_replace('/[^0-9]/', '', $data['telephone']);

                // Ensure calling_code has + prefix if not empty
                if (! empty($data['calling_code']) && ! str_starts_with($data['calling_code'], '+')) {
                    $data['calling_code'] = '+'.ltrim($data['calling_code'], '+');
                }

                $customer = AccountService::getInstance()->registerBySms($data);
                auth('customer')->login($customer);
            } else {
                // Register by email and password
                $credentials = $request->only('email', 'password');
                $customer    = AccountService::getInstance()->register($credentials);
                auth('customer')->attempt($credentials);
            }

            CartService::getInstance(current_customer_id())->mergeCart($oldGuestId);

            return json_success(front_trans('register.register_success'), ['customer' => $customer]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Send SMS verification code for registration
     *
     * @return mixed
     */
    public function sendSmsCode(): mixed
    {
        return $this->sendSmsCodeInternal('register');
    }
}
