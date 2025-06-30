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

class UploadFileRequest extends FormRequest
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
        // Unified security policy - no dangerous file types including SVG
        $allowedMimes = 'jpg,png,jpeg,gif,webp,zip,doc,docx,xls,xlsx,ppt,pptx,pdf,mp4';

        // Dynamic file size limits based on context
        if (request()->is('panel/*') || is_admin()) {
            $maxSize = 8192; // 8MB for admin/panel
        } else {
            $maxSize = 2048; // 2MB for regular users
        }

        return [
            'file' => "required|file|mimes:{$allowedMimes}|max:{$maxSize}",
            'type' => 'required|alpha_dash',
        ];
    }
}
