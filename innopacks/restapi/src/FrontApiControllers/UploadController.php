<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\FrontApiControllers;

use Illuminate\Http\JsonResponse;
use InnoShop\Front\Requests\UploadFileRequest;
use InnoShop\Front\Requests\UploadImageRequest;
use InnoShop\RestAPI\Services\UploadService;

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
        $data = UploadService::getInstance()->images($request);

        return create_json_success($data);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return JsonResponse
     */
    public function files(UploadFileRequest $request): JsonResponse
    {
        $data = UploadService::getInstance()->files($request);

        return create_json_success($data);
    }
}
