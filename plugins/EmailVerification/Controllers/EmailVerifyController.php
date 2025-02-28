<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailVerification\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use InnoShop\Front\Controllers\BaseController;
use Plugin\EmailVerification\Models\Customer;

class EmailVerifyController extends BaseController
{
    /**
     * @return mixed
     * @throws Exception
     */
    public function verified(): mixed
    {
        $customer = current_customer();
        if (! $customer->verified) {
            $code                  = Str::random(32);
            $customer->verify_code = $code;
            $customer->save();

            Customer::query()->find($customer->id)->notifyVerification($code);
        } else {
            return redirect(account_route('index'));
        }

        return view('EmailVerification::account.verified', ['verified' => false]);
    }

    /**
     * @param  Request  $request
     * @return mixed
     */
    public function verify(Request $request): mixed
    {
        $email = $request->get('email');
        $code  = $request->get('code');

        $verified = false;
        if ($email && $code) {
            $customer = Customer::query()->where('email', $email)->where('verify_code', $code)->first();
            if ($customer) {
                $customer->verified = true;
                $customer->save();
                $verified = true;
            }
        }

        return view('EmailVerification::account.verified', ['verified' => $verified]);
    }
}
