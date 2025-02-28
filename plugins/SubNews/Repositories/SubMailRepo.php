<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SubNews\Repositories;

use InnoShop\Common\Repositories\BaseRepo;
use Plugin\SubNews\Models\SubMail;
use Throwable;

class SubMailRepo extends BaseRepo
{
    /**
     * @param  $data
     * @return mixed
     * @throws Throwable
     */
    public function create($data): mixed
    {
        $customerID = $data['customer_id'] ?? 0;
        $subMail    = SubMail::query()->where('email', $data['email'])->first();
        if ($subMail) {
            if ($customerID) {
                $subMail->customer_id = $customerID;
                $subMail->saveOrFail();
            }

            return $subMail;
        }

        $subMail = new SubMail([
            'customer_id' => $customerID,
            'email'       => $data['email'],
            'ip'          => request()->getClientIp(),
            'user_agent'  => request()->userAgent(),
        ]);
        $subMail->saveOrFail();

        return $subMail;
    }
}
