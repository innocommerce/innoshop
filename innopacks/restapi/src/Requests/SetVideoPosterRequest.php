<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SetVideoPosterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Media id (int), or raw storage key — optionally base64-encoded
            // (video_encoded=true) so WAF layers never see media path patterns.
            'video'         => 'required',
            'video_encoded' => 'nullable|boolean',
            // Either a multipart upload or a canvas data URL — the panel posts
            // the base64 variant because some proxy/WAF layers mangle multipart.
            // base64 inflates ~1.37x: 4M chars ≈ 3MB binary, well above any
            // 800x600 canvas frame but bounding decode + getimagesize memory.
            'poster'        => 'required_without:poster_base64|nullable|file|image|mimes:jpg,jpeg,png,webp|max:4096',
            'poster_base64' => 'required_without:poster|nullable|string|max:4194304',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'video' => [
                'description' => 'Target video: a media_files id, or the storage key of a legacy file without one.',
                'example'     => '12',
            ],
            'poster' => [
                'description' => 'Poster frame image (JPEG) captured in the browser from the video. Mutually exclusive with poster_base64.',
                'example'     => 'poster.jpg',
            ],
            'poster_base64' => [
                'description' => 'Poster frame as a data URL (data:image/jpeg;base64,...). Mutually exclusive with poster.',
                'example'     => 'data:image/jpeg;base64,...',
            ],
        ];
    }
}
