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

class PasswordRequest extends FormRequest
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
        return [
            'old_password' => 'required|current_password',
            'new_password' => 'required|confirmed|min:6',
        ];
    }

    public function attributes(): array
    {
        return [
            'old_password' => front_trans('password.old_password'),
            'new_password' => front_trans('password.new_password'),
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
            'old_password' => [
                'description' => 'Current account password.',
                'example'     => 'current-password',
            ],
            'new_password' => [
                'description' => 'New password (min 6 characters; must match new_password_confirmation).',
                'example'     => 'new-secure-password',
            ],
            'new_password_confirmation' => [
                'description' => 'Must match new_password.',
                'example'     => 'new-secure-password',
            ],
        ];
    }
}
