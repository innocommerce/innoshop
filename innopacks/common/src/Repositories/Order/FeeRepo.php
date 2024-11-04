<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Repositories\Order;

use InnoShop\Common\Models\Order;
use InnoShop\Common\Repositories\BaseRepo;

class FeeRepo extends BaseRepo
{
    /**
     * @param  Order  $order
     * @param  $fees
     * @return void
     */
    public function createItems(Order $order, $fees): void
    {
        $orderFees = [];
        foreach ($fees as $item) {
            $orderFees[] = $this->handleItem($order, $item);
        }
        $order->fees()->createMany($orderFees);
    }

    /**
     * @param  Order  $order
     * @param  $requestData
     * @return array
     */
    private function handleItem(Order $order, $requestData): array
    {
        return [
            'order_id'  => $order->id,
            'code'      => $requestData['code'],
            'value'     => $requestData['total'],
            'title'     => $requestData['title'],
            'reference' => $requestData['reference'] ?? '',
        ];
    }
}
