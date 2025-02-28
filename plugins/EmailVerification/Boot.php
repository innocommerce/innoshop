<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailVerification;

use Exception;

class Boot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->handleAccountVerified();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function handleAccountVerified(): void
    {
        listen_hook_action('front.account.account.index.before', function ($data) {
            $customer = current_customer();
            if ($customer->verified) {
                return;
            }

            $url = account_route('verified');
            header('location:'.$url);
            exit;
        });
    }
}
