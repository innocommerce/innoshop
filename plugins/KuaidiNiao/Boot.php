<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\KuaidiNiao;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\KuaidiNiao\Libraries\Kdniao;

class Boot extends BaseBoot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->implementDriver();
    }

    /**
     * @return void
     */
    private function implementDriver(): void
    {
        listen_hook_filter('service.shipping_trace.driver', function ($data) {
            return Kdniao::class;
        });
    }
}
