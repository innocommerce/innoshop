<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RenameFileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'origin_name' => 'required|string|max:255',
            'new_name'    => 'required|string|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'origin_name' => [
                'description' => 'Original file or folder path.',
                'example'     => '/images/photo.jpg',
            ],
            'new_name' => [
                'description' => 'New name (without path separators).',
                'example'     => 'photo_renamed.jpg',
            ],
        ];
    }
}
