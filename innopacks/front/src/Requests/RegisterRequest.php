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

class RegisterRequest extends FormRequest
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
        $authMethod = system_setting('auth_method', 'both');

        $rules = [];

        if ($authMethod === 'email_only' || $authMethod === 'both') {
            $rules['email']    = 'required_without_all:calling_code,telephone|email|unique:customers,email';
            $rules['password'] = 'required_with:email|confirmed';
        }

        if ($authMethod === 'phone_only' || $authMethod === 'both') {
            $rules['calling_code'] = 'required_without:email|string|max:10';
            $rules['telephone']    = 'required_without:email|string|max:20';
            $rules['code']         = 'required_with:calling_code|string|size:6';
        }

        // If only one method is allowed, make it required
        if ($authMethod === 'email_only') {
            $rules['email']    = 'required|email|unique:customers,email';
            $rules['password'] = 'required|confirmed';
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
