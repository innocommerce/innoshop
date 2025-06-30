<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
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
        // Unified security policy for all contexts
        // No SVG support for security reasons
        $allowedMimes = 'jpg,png,jpeg,gif,webp';

        // Dynamic file size limits based on context
        if (request()->is('panel/*') || is_admin()) {
            $maxSize = 8192; // 8MB for admin/panel
        } else {
            $maxSize = 2048; // 2MB for regular users
        }

        return [
            'image' => "required|image|mimes:{$allowedMimes}|max:{$maxSize}",
            'type'  => 'required|alpha_dash',
        ];
    }
}
