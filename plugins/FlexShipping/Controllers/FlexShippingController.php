<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlexShipping\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Panel\Controllers\BaseController;
use Throwable;

class FlexShippingController extends BaseController
{
    /**
     * @throws Throwable
     */
    public function update(Request $request): JsonResponse
    {
        SettingRepo::getInstance()->updatePluginValue('flex_shipping', 'setting', $request->all());

        return json_success('保存成功');
    }
}
