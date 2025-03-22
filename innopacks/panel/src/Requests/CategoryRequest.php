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

class CategoryRequest extends FormRequest
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
        if ($this->category) {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:categories,slug,'.$this->category->id;
        } else {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:categories,slug';
        }

        $defaultLocale = setting_locale_code();

        return [
            'slug'     => $slugRule,
            'position' => 'integer',
            'active'   => 'bool',

            "translations.$defaultLocale.locale" => 'required',
            "translations.$defaultLocale.name"   => 'required',
        ];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        $defaultLocale = setting_locale_code();

        return [
            'slug'                               => panel_trans('common.slug'),
            "translations.$defaultLocale.locale" => trans('panel/category.locale'),
            "translations.$defaultLocale.name"   => trans('panel/category.name'),
        ];
    }
}
