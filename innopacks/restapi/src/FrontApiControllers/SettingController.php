<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\FrontApiControllers;

use Exception;
use Illuminate\Support\Arr;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\Unauthenticated;

#[Group('Front - Settings')]
class SettingController extends BaseController
{
    /**
     * Public storefront/app configuration. Never replace this with a secret-key
     * denylist: plugins can introduce credentials under arbitrary setting names.
     */
    private const PUBLIC_SETTINGS = [
        'country_code',
        'currency',
        'login_checkout',
        'enableInquiry',
        'enableRegister',
        'enableGoogleLogin',
    ];

    /**
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('Get system settings')]
    #[Unauthenticated]
    public function index(): mixed
    {
        $settings = Arr::only(setting('system', []), self::PUBLIC_SETTINGS);

        $settings['locales']    = locales()->select(['name', 'code']);
        $settings['currencies'] = currencies()->select(['name', 'code']);

        return read_json_success($settings);
    }
}
