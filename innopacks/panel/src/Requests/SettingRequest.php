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

class SettingRequest extends FormRequest
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
        $rules = [];

        // Validate panel_name if provided - can be empty (nullable), but if provided must be pure letters
        if ($this->has('panel_name')) {
            $rules['panel_name'] = 'nullable|alpha';
        }

        return $rules;
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array
     */
    public function attributes(): array
    {
        return [
            'panel_name' => panel_trans('setting.panel_name'),
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'panel_name.required' => panel_trans('setting.panel_name_required'),
            'panel_name.alpha'    => panel_trans('setting.panel_name_alpha'),
        ];
    }
}
