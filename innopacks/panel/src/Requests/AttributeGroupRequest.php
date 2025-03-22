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

class AttributeGroupRequest extends FormRequest
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

        return [
            'position'                           => 'integer',
            'translations'                       => 'required|array',
            "translations.$defaultLocale.locale" => 'required',
            "translations.$defaultLocale.name"   => 'required',
        ];
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
            'position'                           => panel_trans('attribute.position'),
            "translations.$defaultLocale.locale" => panel_trans('attribute.locale'),
            "translations.$defaultLocale.name"   => panel_trans('attribute.name'),
        ];
    }
}
