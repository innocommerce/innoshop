<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\BankTransfer;

class Boot
{
    public function init(): void
    {
        listen_hook_filter('service.payment.api.bank_transfer.data', function ($data) {
            $data['params'] = plugin_setting('bank_transfer');

            return $data;
        });
    }
}
