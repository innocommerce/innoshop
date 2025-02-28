<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\EmailVerification\Models;

use Plugin\EmailVerification\Notifications\VerificationNotification;

class Customer extends \InnoShop\Common\Models\Customer
{
    /**
     * @param  $code
     * @return void
     */
    public function notifyVerification($code): void
    {
        $useQueue = system_setting('use_queue', false);

        if ($useQueue) {
            $this->notify(new VerificationNotification($this, $code));
        } else {
            $this->notifyNow(new VerificationNotification($this, $code));
        }
    }
}
