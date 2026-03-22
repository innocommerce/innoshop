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
            $rules['email']    = 'required_without_all:calling_code,telephone|email|unique:customers,email';
            $rules['password'] = 'required_with:email|confirmed';
        }

        if ($authMethod === 'phone_only' || $authMethod === 'both') {
            $rules['calling_code'] = 'nullable|required_without:email|string|max:10';
            $rules['telephone']    = 'nullable|required_without:email|string|max:20';
            $rules['code']         = 'required_with_all:calling_code,telephone|nullable|string|size:6';
        }

        if ($authMethod === 'email_only') {
            $rules['email']        = 'required|email|unique:customers,email';
            $rules['password']     = 'required|confirmed';
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

    /**
     * Extra fields for Scribe / OpenAPI (descriptions & examples).
     *
     * @return array<string, array{description?: string, example?: mixed}>
     */
    public function bodyParameters(): array
    {
        return [
            'email' => [
                'description' => 'Unique email for the new customer account.',
                'example'     => 'newuser@example.com',
            ],
            'password' => [
                'description' => 'Password (must match password_confirmation).',
                'example'     => 'your-secure-password',
            ],
            'password_confirmation' => [
                'description' => 'Same value as password.',
                'example'     => 'your-secure-password',
            ],
            'calling_code' => [
                'description' => 'Phone country calling code for SMS registration.',
                'example'     => '+86',
            ],
            'telephone' => [
                'description' => 'Mobile number without country code.',
                'example'     => '13800138000',
            ],
            'code' => [
                'description' => 'SMS verification code (6 characters).',
                'example'     => '123456',
            ],
        ];
    }
}
