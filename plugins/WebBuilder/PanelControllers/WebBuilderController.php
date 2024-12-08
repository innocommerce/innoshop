<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\PanelControllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Front\Requests\UploadImageRequest;
use InnoShop\Panel\Controllers\BaseController;

class WebBuilderController extends BaseController
{
    /**
     * 显示设计器主页面
     */
    public function index()
    {
        $bestSeller  = ProductRepo::getInstance()->getBestSellerProducts();
        $newArrivals = ProductRepo::getInstance()->getLatestProducts();
        $tabProducts = [
            ['tab_title' => trans('front/home.bestseller'), 'products' => $bestSeller],
            ['tab_title' => trans('front/home.new_arrival'), 'products' => $newArrivals],
        ];

        $news = ArticleRepo::getInstance()->getLatestArticles();
        $data = [
            'tab_products' => $tabProducts,
            'news'         => $news,
        ];

        $data = fire_hook_filter('home.index.data', $data);

        return view('WebBuilder::index', $data);
    }

    /**
     * 上传图片
     *
     * @param  UploadImageRequest  $request
     * @return JsonResponse
     */
    public function uploadImages(UploadImageRequest $request): JsonResponse
    {
        $image    = $request->file('image');
        $type     = $request->input('type', 'banner');
        $filePath = $image->store("/{$type}", 'upload');
        $realPath = "upload/$filePath";

        $data = [
            'url'   => asset($realPath),
            'value' => $realPath,
        ];

        return json_success('上传成功', $data);
    }

    /**
     * 获取设计数据
     *
     * @return JsonResponse
     */
    public function getDesign(): JsonResponse
    {
        $data = plugin_setting('web_builder', 'modules');

        // 确保返回的数据结构正确
        if (! is_array($data)) {
            $data = ['modules' => []];
        } elseif (! isset($data['modules'])) {
            $data = ['modules' => []];
        }

        return json_success('获取成功', $data);
    }

    /**
     * 保存设计数据
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws \Throwable
     */
    public function saveDesign(Request $request): JsonResponse
    {
        $modules = $request->input('modules', []);

        // 保存模块数据
        SettingRepo::getInstance()->updatePluginValue('web_builder', 'modules', [
            'modules' => $modules,
        ]);

        return json_success('保存成功');
    }
}
