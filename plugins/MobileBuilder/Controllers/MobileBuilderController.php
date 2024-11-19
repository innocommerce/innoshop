<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MobileBuilder\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Front\Requests\UploadImageRequest;
use InnoShop\Panel\Controllers\BaseController;

class MobileBuilderController extends BaseController
{
    public function index()
    {
        return view('MobileBuilder::index');
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

    /**
     * Get mobile builder design data
     *
     * @return JsonResponse
     */
    public function getDesign(): JsonResponse
    {
        $data = plugin_setting('mobile_builder', 'modules');

        return json_success('获取成功', $data);
    }

    /**
     * Save mobile builder design data
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function saveDesign(Request $request): JsonResponse
    {
        SettingRepo::getInstance()->updatePluginValue('mobile_builder', 'modules', $request->all());

        return json_success('保存成功');
    }
}
