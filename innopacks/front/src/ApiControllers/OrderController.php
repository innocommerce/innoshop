<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\ApiControllers;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use InnoShop\Common\Repositories\OrderRepo;

class OrderController extends BaseApiController
{
    /**
     * @return LengthAwarePaginator
     */
    public function index(): LengthAwarePaginator
    {
        return OrderRepo::getInstance()->list();
    }
}
