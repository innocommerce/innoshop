<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Requests;

use Illuminate\Foundation\Http\FormRequest;
use InnoShop\Common\Services\CheckoutService;

class CheckoutConfirmRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     * @throws \Throwable
     */
    public function rules(): array
    {
        $rules = [
            'billing_method_code' => 'required|string',
        ];

        $isVirtual = CheckoutService::getInstance()->checkIsVirtual();
        if (! $isVirtual) {
            $rules['shipping_address_id']  = 'required|integer';
            $rules['billing_address_id']   = 'required|integer';
            $rules['shipping_method_code'] = 'required|string';
        }

        return $rules;
    }
}
