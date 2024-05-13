<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\ApiControllers;

use InnoShop\Common\Repositories\CategoryRepo;

class CategoryController extends BaseApiController
{
    public function index()
    {
        $categories = CategoryRepo::getInstance()->withActive()->builder()->get();

        return json_success('获取成功', $categories);
    }
}
