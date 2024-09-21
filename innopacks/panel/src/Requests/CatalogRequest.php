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

class CatalogRequest extends FormRequest
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
        if ($this->catalog) {
            $slugRule = 'required|alpha_dash|unique:articles,slug,'.$this->catalog->id;
        } else {
            $slugRule = 'required|alpha_dash|unique:articles,slug';
        }

        return [
            'slug'     => $slugRule,
            'position' => 'integer',
            'active'   => 'bool',

            'translations.*.locale' => 'required',
            'translations.*.title'  => 'required',
        ];
    }
}
