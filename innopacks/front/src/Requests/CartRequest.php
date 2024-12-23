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
use Throwable;

class CartRequest extends FormRequest
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
     * @throws Throwable
     */
    public function rules(): array
    {
        $rules = [
            'quantity' => 'required|integer|min:1',
        ];

        if (empty($this->cart->id)) {
            $rules['sku_id'] = 'required|integer';
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'quantity' => trans('front/cart.quantity'),
        ];
    }
}
