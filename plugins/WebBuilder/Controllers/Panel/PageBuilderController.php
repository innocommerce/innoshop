<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\Controllers\Panel;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\PageModule;
use InnoShop\Common\Repositories\ArticleRepo;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Repositories\ProductRepo;
use InnoShop\Panel\Controllers\BaseController;
use Throwable;

class PageBuilderController extends BaseController
{
    /**
     * 单页编辑主页面
     *
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
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
            'pages'        => PageRepo::getInstance()->all(),
        ];

        $data = fire_hook_filter('home.index.data', $data);

        return view('WebBuilder::index', $data);
    }

    /**
     * 获取设计数据
     *
     * @param  Page  $page
     * @return JsonResponse
     */
    public function getDesign(Page $page): JsonResponse
    {
        $pageModule = PageModule::query()->where('page_id', $page->id)->first();

        $data = [
            'modules' => $pageModule->module_data ?? [],
        ];

        return json_success('获取成功', $data);
    }

    /**
     * 保存设计数据
     *
     * @param  Request  $request
     * @param  Page  $page
     * @return JsonResponse
     * @throws Throwable
     */
    public function saveDesign(Request $request, Page $page): JsonResponse
    {
        try {
            $pageModule = PageModule::query()->where('page_id', $page->id)->first();
            $modules    = $request->input('modules', []);
            if (empty($pageModule)) {
                $pageModule = new PageModule;
            }

            $pageModule->fill([
                'page_id'     => $page->id,
                'module_data' => $modules,
            ]);
            $pageModule->saveOrFail();

            return json_success('保存成功');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
