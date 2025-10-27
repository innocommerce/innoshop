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

class StateRequest extends FormRequest
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
        if ($this->state) {
            // 更新时：在同一个国家内，code 必须唯一，但排除当前记录
            $codeRule = 'alpha_dash|unique:states,code,'.$this->state->id.',id,country_id,'.$this->input('country_id');
        } else {
            // 创建时：在同一个国家内，code 必须唯一
            $codeRule = 'alpha_dash|unique:states,code,NULL,id,country_id,'.$this->input('country_id');
        }

        return [
            'name'       => 'required|string|max:64',
            'code'       => $codeRule,
            'country_id' => 'required|integer',
            'position'   => 'integer',
            'active'     => 'boolean',
        ];
    }
}
