<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerGroupRequest extends FormRequest
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
     */
    public function rules(): array
    {
        $defaultLocale = setting_locale_code();

        $rules = [
            'level'                              => 'required|integer',
            'mini_cost'                          => 'required|numeric',
            'discount_rate'                      => 'required|numeric|between:0,100',
            'translations'                       => 'required|array',
            "translations.$defaultLocale.locale" => 'required',
            "translations.$defaultLocale.name"   => 'required',
        ];

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        $defaultLocale = setting_locale_code();

        return [
            'level'                              => panel_trans('customer.level'),
            'mini_cost'                          => panel_trans('customer.mini_cost'),
            'discount_rate'                      => panel_trans('customer.discount_rate'),
            "translations.$defaultLocale.locale" => panel_trans('customer.locale'),
            "translations.$defaultLocale.name"   => panel_trans('customer.group'),
        ];
    }
}
