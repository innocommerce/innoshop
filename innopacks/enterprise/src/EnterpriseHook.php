<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise;

use Throwable;

class EnterpriseHook
{
    public static function getInstance(): EnterpriseHook
    {
        return new self;
    }

    /**
     * @return void
     * @throws Throwable
     */
    public function init(): void {}
}
