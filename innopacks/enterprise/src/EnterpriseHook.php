<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Enterprise;

use InnoShop\Enterprise\Hooks\Currency;
use InnoShop\Enterprise\Hooks\Product;

class EnterpriseHook
{
    public static function getInstance(): EnterpriseHook
    {
        return new self;
    }

    /**
     * @return void
     */
    public function init(): void
    {
        Product::getInstance()->init();
        Currency::getInstance()->init();
    }
}
