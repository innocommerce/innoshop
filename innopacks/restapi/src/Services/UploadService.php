<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Services;

use InnoShop\Front\Requests\UploadFileRequest;
use InnoShop\Front\Requests\UploadImageRequest;
use InnoShop\Front\Services\BaseService;

class UploadService extends BaseService
{
    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return array
     */
    public function images(UploadImageRequest $request): array
    {
        $image    = $request->file('image');
        $type     = $request->file('type', 'common');
        $filePath = $image->store("/{$type}", 'upload');
        $realPath = "upload/$filePath";

        return [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return array
     */
    public function files(UploadFileRequest $request): array
    {
        $file     = $request->file('file');
        $type     = $request->file('type', 'files');
        $filePath = $file->store("/{$type}", 'upload');
        $realPath = "upload/$filePath";

        return [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];
    }
}
