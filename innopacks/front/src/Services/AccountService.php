<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Services;

use InnoShop\Common\Models\Customer;
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
            'password' => $data['password'],
            'active'   => 1,
        ];

        return CustomerRepo::getInstance()->create($customerData);
    }
}
