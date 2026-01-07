<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use Exception;
use InnoShop\Front\Services\AccountService;

trait SendSmsCodeTrait
{
    /**
     * Send SMS verification code (internal method)
     *
     * @param  string  $type
     * @return mixed
     */
    protected function sendSmsCodeInternal(string $type): mixed
    {
        try {
            $request = request();
            $request->validate([
                'calling_code' => 'required|string|max:10',
                'telephone'    => 'required|string|max:20',
            ]);

            AccountService::getInstance()->sendSmsCode(
                $request->input('calling_code'),
                $request->input('telephone'),
                $type
            );

            return json_success(front_trans("{$type}.sms_code_sent"));
        } catch (Exception $e) {
            return $this->handleSmsError($e);
        }
    }

    /**
     * Handle SMS error with unified error format
     *
     * @param  Exception  $e
     * @return mixed
     */
    protected function handleSmsError(Exception $e): mixed
    {
        $errorMessage      = $e->getMessage();
        $translatedMessage = __('common/sms.send_failed', ['message' => $errorMessage]);

        return json_fail($translatedMessage);
    }
}
