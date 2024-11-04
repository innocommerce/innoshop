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

class CountryRequest extends FormRequest
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
        if ($this->country) {
            $slugRule = 'alpha_dash|unique:countries,code,'.$this->country->id;
        } else {
            $slugRule = 'alpha_dash|unique:countries,code';
        }

        return [
            'name'      => 'string|required|max:32',
            'code'      => $slugRule,
            'continent' => 'string|required',
            'position'  => 'integer',
            'active'    => 'bool',
        ];
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'name'      => panel_trans('common.name'),
            'code'      => panel_trans('common.code'),
            'continent' => panel_trans('country.continent'),
            'position'  => panel_trans('common.position'),
        ];
    }
}
