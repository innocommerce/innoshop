<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\ApiControllers;

use Illuminate\Http\JsonResponse;
use InnoShop\Common\Repositories\ProductRepo;

class ProductController extends BaseApiController
{
    /**
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $categories = ProductRepo::getInstance()->withActive()->builder()->paginate();

        return json_success('获取成功', $categories);
    }
}
