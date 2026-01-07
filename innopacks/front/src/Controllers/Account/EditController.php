<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers\Account;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\CustomerRepo;
use InnoShop\Common\Services\SmsService;
use InnoShop\Front\Services\AccountService;

class EditController extends Controller
{
    use SendSmsCodeTrait;

    /**
     * @return mixed
     */
    public function index(): mixed
    {
        return inno_view('account.edit');
    }

    /**
     * Send SMS verification code for updating phone number
     *
     * @return mixed
     */
    public function sendSmsCode(): mixed
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
                'update_phone'
            );

            return json_success(front_trans('edit.sms_code_sent'));
        } catch (Exception $e) {
            return $this->handleSmsError($e);
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function update(Request $request): mixed
    {
        try {
            $customer = current_customer();
            $data     = $request->only(['avatar', 'name', 'email']);

            // Handle phone number update with verification code
            // Only require verification code if phone number is being changed or added
            $callingCode = trim($request->input('calling_code', ''));
            $telephone   = trim($request->input('telephone', ''));
            $code        = trim($request->input('code', ''));

            // Get current phone number (handle null values)
            $currentCallingCode = $customer->calling_code ?? '';
            $currentTelephone   = $customer->telephone ?? '';

            // If phone fields are provided, process them
            if (! empty($callingCode) && ! empty($telephone)) {
                // Remove any non-digit characters from telephone
                $telephone = preg_replace('/[^0-9]/', '', $telephone);

                // Ensure calling_code has + prefix if not empty
                if (! empty($callingCode) && ! str_starts_with($callingCode, '+')) {
                    $callingCode = '+'.ltrim($callingCode, '+');
                }

                // Check if phone number is being changed or added
                $isPhoneChanged = ($currentCallingCode !== $callingCode) || ($currentTelephone !== $telephone);

                if ($isPhoneChanged) {
                    // Phone number is being changed or added, verification code is required
                    // The verification code should be sent to the NEW phone number
                    if (empty($code)) {
                        throw new Exception(front_trans('edit.code_required'));
                    }

                    $smsService = new SmsService;
                    // Verify code sent to the new phone number
                    if (! $smsService->verifyCode($callingCode, $telephone, $code, 'update_phone')) {
                        throw new Exception(front_trans('account.verify_code_error'));
                    }

                    // Check if phone number is already used by another customer
                    $customerRepo     = CustomerRepo::getInstance();
                    $existingCustomer = $customerRepo->findByPhone($callingCode, $telephone);
                    if ($existingCustomer && $existingCustomer->id !== $customer->id) {
                        throw new Exception(front_trans('edit.phone_already_used'));
                    }

                    // Update phone number
                    $data['calling_code'] = $callingCode;
                    $data['telephone']    = $telephone;

                    // Delete verification code after successful update
                    $smsService->deleteCode($callingCode, $telephone);
                } else {
                    // Phone number is not changed, keep existing values (no verification needed)
                    if (! empty($currentCallingCode)) {
                        $data['calling_code'] = $currentCallingCode;
                    }
                    if (! empty($currentTelephone)) {
                        $data['telephone'] = $currentTelephone;
                    }
                }
            } else {
                // Phone fields are empty, clear them if they exist
                // (User wants to remove phone number)
                $data['calling_code'] = null;
                $data['telephone']    = null;
            }

            CustomerRepo::getInstance()->updateProfile($customer, $data);

            return redirect(account_route('edit.index'))
                ->with('instance', $customer)
                ->with('success', front_trans('common.updated_success'));

        } catch (Exception $e) {
            return redirect(account_route('edit.index'))
                ->withInput()
                ->with(['error' => $e->getMessage()]);
        }
    }
}
