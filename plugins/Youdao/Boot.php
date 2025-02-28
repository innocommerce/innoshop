<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Youdao;

use Plugin\Youdao\Services\YoudaoService;

class Boot
{
    public function init(): void
    {
        listen_hook_filter('panel.service.translator', function ($data) {
            return YoudaoService::class;
        });
    }
}
