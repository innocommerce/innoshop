<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Controllers;

use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Common\Requests\UploadImageRequest;
use InnoShop\RestAPI\Services\UploadService;

class UploadController
{
    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return mixed
     */
    public function images(UploadImageRequest $request): mixed
    {
        $data = UploadService::getInstance()->images($request);

        return json_success(trans('common/upload.upload_success'), $data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    public function docs(UploadFileRequest $request): mixed
    {
        $data = UploadService::getInstance()->files($request);

        return json_success(trans('common/upload.upload_success'), $data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return mixed
     */
    public function files(UploadFileRequest $request): mixed
    {
        $data = UploadService::getInstance()->files($request);

        return json_success(trans('common/upload.upload_success'), $data);
    }
}
