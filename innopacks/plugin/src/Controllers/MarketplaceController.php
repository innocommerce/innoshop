<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Plugin\Services\MarketplaceService;
use Throwable;

class MarketplaceController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws ConnectionException
     */
    public function quickCheckout(Request $request): mixed
    {
        $data = $request->all();

        return MarketplaceService::getInstance()->quickCheckout($data);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function updateDomainToken(Request $request): JsonResponse
    {
        try {
            $domainToken = $request->get('domain_token');
            SettingRepo::getInstance()->updateSystemValue('domain_token', $domainToken);

            return json_success(trans('panel::common.updated_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  int  $slug
     * @return JsonResponse
     */
    public function download(int $slug): JsonResponse
    {
        try {
            MarketplaceService::getInstance()->download($slug);

            return json_success('下载成功, 请去插件或主题列表安装使用');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
