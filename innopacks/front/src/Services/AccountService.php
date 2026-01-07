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
use InnoShop\Common\Services\SmsService;
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
        $authMethod = system_setting('auth_method', 'both');

        // Validate auth method
        if ($authMethod === 'email_only' && (! isset($data['email']) || empty($data['email']))) {
            throw new Exception(front_trans('register.email_required'));
        }

        if ($authMethod === 'phone_only' && (! isset($data['calling_code']) || ! isset($data['telephone']))) {
            throw new Exception(front_trans('register.phone_required'));
        }

        $customerData = [
            'password' => $data['password'] ?? '',
            'from'     => $this->checkFrom(),
            'active'   => 1,
        ];

        // Register by email or phone
        if (isset($data['email']) && ! empty($data['email'])) {
            $email                 = $data['email'];
            $parseData             = explode('@', $email);
            $customerData['email'] = $email;
            $customerData['name']  = $parseData[0];
        } elseif (isset($data['calling_code']) && isset($data['telephone'])) {
            $customerData['calling_code'] = $data['calling_code'];
            $customerData['telephone']    = $data['telephone'];
            $customerData['name']         = $data['telephone'];
            // Email can be empty for phone registration
            $customerData['email'] = null;
        } else {
            throw new Exception('Email or phone number is required');
        }

        $customer = CustomerRepo::getInstance()->create($customerData);

        fire_hook_action('front.service.account.register', $customer);

        // Only send registration notification if email is provided
        if (! empty($customerData['email'])) {
            $customer->notifyRegistration();
        }

        return $customer;
    }

    /**
     * Register by SMS verification code
     *
     * @param  array  $data
     * @return Customer
     * @throws Exception
     */
    public function registerBySms(array $data): Customer
    {
        // Clean and format phone data
        $callingCode = trim($data['calling_code'] ?? '');
        $telephone   = trim($data['telephone'] ?? '');
        $code        = trim($data['code'] ?? '');

        // Remove any non-digit characters from telephone
        $telephone = preg_replace('/[^0-9]/', '', $telephone);

        // Ensure calling_code has + prefix if not empty
        if (! empty($callingCode) && ! str_starts_with($callingCode, '+')) {
            $callingCode = '+'.ltrim($callingCode, '+');
        }

        // Validate required fields
        if (empty($callingCode) || empty($telephone) || empty($code)) {
            throw new Exception(front_trans('account.verify_code_error'));
        }

        // Verify SMS code
        $smsService = new SmsService;
        if (! $smsService->verifyCode($callingCode, $telephone, $code, 'register')) {
            throw new Exception(front_trans('account.verify_code_error'));
        }

        // Check if customer already exists
        $customerRepo     = CustomerRepo::getInstance();
        $existingCustomer = $customerRepo->findByPhone($callingCode, $telephone);

        if ($existingCustomer) {
            // Customer exists, just login
            $smsService->deleteCode($callingCode, $telephone);

            return $existingCustomer;
        }

        // Create new customer
        $customerData = [
            'calling_code' => $callingCode,
            'telephone'    => $telephone,
            'name'         => $telephone,
            'email'        => null, // Email can be empty for phone registration
            'password'     => '',
            'from'         => $this->checkFrom(),
            'active'       => 1,
        ];

        $customer = $customerRepo->create($customerData);
        $smsService->deleteCode($callingCode, $telephone);

        fire_hook_action('front.service.account.register', $customer);

        return $customer;
    }

    /**
     * Login by SMS verification code
     *
     * @param  array  $data
     * @return Customer
     * @throws Exception
     */
    public function loginBySms(array $data): Customer
    {
        // Clean and format phone data
        $callingCode = trim($data['calling_code'] ?? '');
        $telephone   = trim($data['telephone'] ?? '');
        $code        = trim($data['code'] ?? '');

        // Remove any non-digit characters from telephone
        $telephone = preg_replace('/[^0-9]/', '', $telephone);

        // Ensure calling_code has + prefix if not empty
        if (! empty($callingCode) && ! str_starts_with($callingCode, '+')) {
            $callingCode = '+'.ltrim($callingCode, '+');
        }

        // Validate required fields
        if (empty($callingCode) || empty($telephone) || empty($code)) {
            throw new Exception(front_trans('account.verify_code_error'));
        }

        // Verify SMS code
        $smsService = new SmsService;
        if (! $smsService->verifyCode($callingCode, $telephone, $code, 'login')) {
            throw new Exception(front_trans('account.verify_code_error'));
        }

        // Find customer
        $customerRepo = CustomerRepo::getInstance();
        $customer     = $customerRepo->findByPhone($callingCode, $telephone);

        if (! $customer) {
            // Auto register if not exists
            $customer = $this->registerBySms($data);
        } else {
            $smsService->deleteCode($callingCode, $telephone);
        }

        if (! $customer->active) {
            throw new Exception(front_trans('login.inactive_customer'));
        }

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

        // Delete old verification codes of the same type
        VerifyCode::query()
            ->where('account', $email)
            ->where('type', 'reset')
            ->delete();

        VerifyCode::query()->create([
            'account' => $email,
            'code'    => $code,
            'type'    => 'reset',
        ]);

        $customer = CustomerRepo::getInstance()->builder()->where('email', $email)->first();
        if ($customer) {
            $customer->notifyForgotten($code);
        }
    }

    /**
     * Send SMS verification code
     *
     * @param  string  $callingCode
     * @param  string  $telephone
     * @param  string  $type
     * @return void
     * @throws Exception
     */
    public function sendSmsCode(string $callingCode, string $telephone, string $type = 'register'): void
    {
        // Use dependency injection if available, otherwise create new instance
        $smsService = app(SmsService::class);
        $smsService->sendVerificationCode($callingCode, $telephone, $type);
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
        $verifyCode = VerifyCode::query()
            ->where('account', $account)
            ->where('code', $code)
            ->where('type', 'reset')
            ->first();

        if (! $verifyCode) {
            throw new Exception(front_trans('account.verify_code_error'));
        }

        if ($verifyCode->created_at->addMinutes(10) < Carbon::now()) {
            $verifyCode->delete();

            throw new Exception(front_trans('account.verify_code_expired'));
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
            return Customer::FROM_MINIAPP;
        } elseif (is_wechat_official()) {
            return Customer::FROM_WECHAT_OFFICIAL;
        } elseif (is_mobile()) {
            return Customer::FROM_MOBILE_WEB;
        } elseif (is_app()) {
            return Customer::FROM_APP;
        } else {
            return Customer::FROM_PC_WEB;
        }
    }
}
