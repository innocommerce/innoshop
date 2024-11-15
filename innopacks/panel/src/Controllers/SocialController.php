<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\Customer\SocialRepo;
use InnoShop\Common\Repositories\SettingRepo;
use Throwable;

class SocialController extends BaseController
{
    public function index()
    {
        $data = [
            'providers' => SocialRepo::getInstance()->getProviders(),
            'socials'   => system_setting('social', []),
        ];

        return inno_view('panel::socials.index', $data);
    }

    /**
     * @param  Request  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $data = $request->all();
            SettingRepo::getInstance()->updateSystemValue('social', $data);

            return update_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
