<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use Illuminate\Http\JsonResponse;
use InnoShop\Front\Requests\UploadFileRequest;
use InnoShop\Front\Requests\UploadImageRequest;

class UploadController
{
    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return JsonResponse
     */
    public function images(UploadImageRequest $request): JsonResponse
    {
        $image    = $request->file('image');
        $type     = $request->file('type', 'common');
        $filePath = $image->store("/{$type}", 'public');
        $realPath = "storage/$filePath";

        $data = [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];

        return json_success('上传成功', $data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return JsonResponse
     */
    public function files(UploadFileRequest $request): JsonResponse
    {
        $file     = $request->file('file');
        $type     = $request->file('type', 'files');
        $filePath = $file->store("/{$type}", 'public');
        $realPath = "storage/$filePath";

        $data = [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];

        return json_success('上传成功', $data);
    }
}
