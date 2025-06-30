<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\Services;

use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Common\Requests\UploadImageRequest;
use InnoShop\Common\Services\FileSecurityValidator;
use InnoShop\Front\Services\BaseService;

class UploadService extends BaseService
{
    /**
     * Generic upload method for different file types and storage disks
     *
     * @param  mixed  $file  The uploaded file
     * @param  string  $type  File type/directory
     * @param  string  $disk  Storage disk name
     * @param  string  $pathPrefix  Path prefix for the final URL
     * @return array
     */
    public function uploadFile($file, string $type = 'common', string $disk = 'upload', string $pathPrefix = 'static/uploads'): array
    {
        // Unified security validation - this is the single point of validation
        FileSecurityValidator::validateFile($file->getClientOriginalName());

        $filePath = $file->store("/{$type}", $disk);
        $realPath = "{$pathPrefix}/$filePath";

        return [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];
    }

    /**
     * Upload for Panel (using catalog disk)
     *
     * @param  mixed  $file  The uploaded file
     * @param  string  $type  File type/directory
     * @return array
     */
    public function uploadForPanel($file, string $type = 'common'): array
    {
        return $this->uploadFile($file, $type, 'catalog', 'catalog');
    }

    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return array
     */
    public function images(UploadImageRequest $request): array
    {
        $image = $request->file('image');
        $type  = $request->file('type', 'common');

        return $this->uploadFile($image, $type);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return array
     */
    public function docs(UploadFileRequest $request): array
    {
        $file = $request->file('file');
        $type = $request->file('type', 'docs');

        return $this->uploadFile($file, $type);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return array
     */
    public function files(UploadFileRequest $request): array
    {
        $file = $request->file('file');
        $type = $request->file('type', 'files');

        return $this->uploadFile($file, $type);
    }
}
