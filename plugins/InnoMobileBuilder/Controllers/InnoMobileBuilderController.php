<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InnoMobileBuilder\Controllers;

use Illuminate\Http\JsonResponse;
use InnoShop\Front\Requests\UploadImageRequest;
use InnoShop\Panel\Controllers\BaseController;

class InnoMobileBuilderController extends BaseController
{
    public function index()
    {
        return view('InnoMobileBuilder::index');
    }

    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return JsonResponse
     */
    public function uploadImages(UploadImageRequest $request): JsonResponse
    {
        $image    = $request->file('image');
        $type     = $request->file('type', 'banner');
        $filePath = $image->store("/{$type}", 'upload');
        $realPath = "upload/$filePath";

        $data = [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];

        return json_success('上传成功', $data);
    }
}
