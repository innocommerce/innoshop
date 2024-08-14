<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Customer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use InnoShop\Common\Models\Customer;
use InnoShop\Common\Models\Customer\Social;
use InnoShop\Common\Repositories\BaseRepo;
use InnoShop\Front\Services\AccountService;
use Throwable;

class SocialRepo extends BaseRepo
{
    /**
     * @param  $provider
     * @param  array  $userData
     * @return Customer
     * @throws Throwable
     */
    public function createCustomer($provider, array $userData): Customer
    {
        $social   = $this->getSocialByProviderAndUser($provider, $userData['uid']);
        $customer = $social->customer ?? null;
        if ($customer) {
            return $customer;
        }

        $email = $userData['email'];
        if (empty($email)) {
            $email = strtolower(Str::random(8))."@{$provider}.com";
        }
        $customer = Customer::query()->where('email', $email)->first();
        if (empty($customer)) {
            $customerData = [
                'from'   => $provider,
                'email'  => $email,
                'name'   => $userData['name'],
                'avatar' => $userData['avatar'],
            ];
            $customer = AccountService::getInstance()->register($customerData);
        }

        $this->createSocial($customer, $provider, $userData);

        return $customer;
    }

    /**
     * @param  $customer
     * @param  $provider
     * @param  array  $userData
     * @return Model|Builder
     */
    public function createSocial($customer, $provider, array $userData): Model|Builder
    {
        $social = $this->getSocialByProviderAndUser($provider, $userData['uid']);
        if ($social) {
            return $social;
        }

        $socialData = [
            'customer_id'   => $customer->id,
            'provider'      => $provider,
            'user_id'       => $userData['uid'],
            'union_id'      => '',
            'access_token'  => $userData['token'],
            'refresh_token' => $userData['refresh_token'] ?? '',
            'reference'     => json_encode($userData['raw']),
        ];

        return Social::query()->create($socialData);
    }

    /**
     * @param  $provider
     * @param  $userId
     * @return Social|null
     */
    public function getSocialByProviderAndUser($provider, $userId): ?Social
    {
        return Social::query()
            ->with(['customer'])
            ->where('provider', $provider)
            ->where('user_id', $userId)
            ->first();
    }

    /**
     * @param  $provider
     * @param  $customerID
     * @return Social|null
     */
    public function getSocialByProviderAndCustomer($provider, $customerID): ?Social
    {
        return Social::query()
            ->with(['customer'])
            ->where('provider', $provider)
            ->where('customer_id', $customerID)
            ->first();
    }
}
