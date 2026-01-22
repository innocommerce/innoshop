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

class LoginRequest extends FormRequest
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
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $data = $this->all();

        $phoneFields = ['calling_code', 'telephone', 'code'];
        foreach ($phoneFields as $field) {
            if (isset($data[$field]) && trim($data[$field]) === '') {
                $data[$field] = null;
            }
        }

        $this->merge($data);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $authMethod = auth_method();

        $rules = [];

        if ($authMethod === 'email_only' || $authMethod === 'both') {
            $rules['email']    = 'required_without_all:calling_code,telephone';
            $rules['password'] = 'required_with:email';
        }

        if ($authMethod === 'phone_only' || $authMethod === 'both') {
            $rules['calling_code'] = 'nullable|required_without:email|string|max:10';
            $rules['telephone']    = 'nullable|required_without:email|string|max:20';
            $rules['code']         = 'required_with_all:calling_code,telephone|nullable|string|size:6';
        }

        if ($authMethod === 'email_only') {
            $rules['email']        = 'required';
            $rules['password']     = 'required';
            $rules['calling_code'] = 'nullable';
            $rules['telephone']    = 'nullable';
            $rules['code']         = 'nullable';
        } elseif ($authMethod === 'phone_only') {
            $rules['calling_code'] = 'required|string|max:10';
            $rules['telephone']    = 'required|string|max:20';
            $rules['code']         = 'required|string|size:6';
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        return [
            'email'    => front_trans('login.email'),
            'password' => front_trans('login.password'),
        ];
    }
}
