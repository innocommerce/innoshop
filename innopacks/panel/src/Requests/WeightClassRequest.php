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
use Illuminate\Validation\Rule;

class WeightClassRequest extends FormRequest
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
        $rules = [
            'name'     => 'required|string|max:255',
            'unit'     => 'required|string|max:10',
            'value'    => 'required|numeric|min:0.000001',
            'position' => 'required|integer|min:0',
            'active'   => 'boolean',
        ];

        // Validate code uniqueness for new records
        if ($this->isMethod('post')) {
            $rules['code'] = 'required|string|max:10|unique:weight_classes,code';
        }

        // Validate code uniqueness for updates (excluding current record)
        if ($this->isMethod('put') || $this->isMethod('patch')) {
            $id            = $this->route('weight_class');
            $rules['code'] = [
                'required',
                'string',
                'max:10',
                Rule::unique('weight_classes', 'code')->ignore($id),
            ];
        }

        // Check if this is the default weight class
        $defaultCode = system_setting('default_weight_class');
        if ($defaultCode && $this->code === $defaultCode) {
            $rules['value'] = 'required|numeric|in:1';
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
            'name'     => panel_trans('weight_class.name'),
            'code'     => panel_trans('weight_class.code'),
            'unit'     => panel_trans('weight_class.unit'),
            'value'    => panel_trans('weight_class.value'),
            'position' => panel_trans('weight_class.position'),
            'active'   => panel_trans('weight_class.active'),
        ];
    }
}
