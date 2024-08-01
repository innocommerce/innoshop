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
use Illuminate\Http\JsonResponse;
use InnoShop\Common\Services\CartService;
use InnoShop\Front\Requests\RegisterRequest;
use InnoShop\Front\Services\AccountService;

class RegisterController extends Controller
{
    /**
     * @return mixed
     */
    public function index(): mixed
    {
        if (current_customer()) {
            return redirect(front_route('account.index'));
        }

        return inno_view('account.register');
    }

    /**
     * @param  RegisterRequest  $request
     * @return JsonResponse
     */
    public function store(RegisterRequest $request): JsonResponse
    {
        try {
            $oldGuestId  = current_guest_id();
            $credentials = $request->only('email', 'password');
            AccountService::getInstance()->register($credentials);
            auth('customer')->attempt($credentials);

            CartService::getInstance(current_customer_id())->mergeCart($oldGuestId);

            fire_hook_action('front.account.register.after', current_customer());
            
            return json_success('注册成功');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
