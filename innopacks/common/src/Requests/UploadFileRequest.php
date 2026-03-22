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
        if (request()->is('panel/*') || request()->is('api/panel/*') || is_admin()) {
            // For admin/panel, use server's PHP ini settings
            $uploadMaxFileSize = ini_get('upload_max_filesize');
            $postMaxSize       = ini_get('post_max_size');

            // Convert to bytes and then to KB (Laravel validation uses KB)
            $uploadMaxFileSizeBytes = ini_size_to_bytes($uploadMaxFileSize);
            $postMaxSizeBytes       = ini_size_to_bytes($postMaxSize);

            // Use the smaller of the two values
            $maxSizeBytes = min($uploadMaxFileSizeBytes, $postMaxSizeBytes);

            // Convert to KB for Laravel validation
            $maxSize = (int) ($maxSizeBytes / 1024);
        } else {
            // For regular users, read from system settings, default 2MB
            $maxSize = (int) system_setting('upload_max_file_size', 2048);
        }

        return [
            'file' => "required|file|mimes:{$allowedMimes}|max:{$maxSize}",
            'type' => 'required|alpha_dash',
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
            'file' => [
                'description' => 'File upload (multipart). Allowed types include images, zip, office docs, pdf, mp4.',
                'example'     => null,
            ],
            'type' => [
                'description' => 'Upload context slug (alphanumeric + dash/underscore).',
                'example'     => 'document',
            ],
        ];
    }
}
