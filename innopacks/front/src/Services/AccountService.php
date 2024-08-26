<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

use Carbon\Carbon;
use Exception;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\VerifyCode;
use InnoShop\Common\Repositories\CustomerRepo;
use Throwable;

class AccountService extends BaseService
{
    /**
     * @param  $data
     * @return Customer
     * @throws Throwable
     */
    public function register($data): Customer
    {
        $email        = $data['email'];
        $parseData    = explode('@', $email);
        $customerData = [
            'email'    => $data['email'],
            'name'     => $parseData[0],
            'password' => $data['password'] ?? '',
            'from'     => $this->checkFrom(),
            'active'   => 1,
        ];

        $customer = CustomerRepo::getInstance()->create($customerData);
        $customer->notifyRegistration();

        return $customer;
    }

    /**
     * Send verify code by email.
     *
     * @param  $email
     * @return void
     */
    public function sendVerifyCode($email): void
    {
        $code = mt_rand(100000, 999999);

        VerifyCode::query()->where('account', $email)->delete();
        VerifyCode::query()->create([
            'account' => $email,
            'code'    => $code,
        ]);

        $customer = CustomerRepo::getInstance()->builder()->where('email', $email)->first();
        if ($customer) {
            $customer->notifyForgotten($code);
        }
    }

    /**
     * @param  $code
     * @param  $account
     * @param  $password
     * @return void
     * @throws Exception
     */
    public function verifyUpdatePassword($code, $account, $password): void
    {
        $verifyCode = VerifyCode::query()->where('account', $account)->first();
        if ($verifyCode->created_at->addMinutes(10) < Carbon::now()) {
            $verifyCode->delete();

            throw new Exception(front_trans('account.verify_code_expired'));
        }

        if ($verifyCode->code != $code) {
            throw new Exception(front_trans('account.verify_code_error'));
        }

        $customer = CustomerRepo::getInstance()->findByEmail($account);
        if (! $customer) {
            throw new Exception(front_trans('account.account_not_exist'));
        }

        CustomerRepo::getInstance()->forceUpdatePassword($customer, $password);
        $verifyCode->delete();
    }

    /**
     * @return string
     */
    private function checkFrom(): string
    {
        if (is_wechat_mini()) {
            return 'miniapp';
        } elseif (is_wechat_official()) {
            return 'wechat_official';
        } elseif (is_mobile()) {
            return 'mobile_web';
        } elseif (is_app()) {
            return 'app';
        } else {
            return 'pc_web';
        }
    }
}
