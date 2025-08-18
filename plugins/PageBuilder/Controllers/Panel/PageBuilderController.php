<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Controllers\Panel;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\PageBuilder\Services\DesignService;
use Plugin\PageBuilder\Services\PageBuilderService;

class PageBuilderController extends BaseController
{
    protected PageBuilderService $pageBuilderService;

    public function __construct()
    {
        $this->pageBuilderService = new PageBuilderService;
    }

    /**
     * 页面编辑主页面 - 统一处理首页和单页
     *
     * @param  string|null  $page  页面标识，'home' 表示首页，其他为页面ID或slug，null表示首页
     * @return mixed
     * @throws Exception
     */
    public function index(?string $page = null): mixed
    {
        $data           = $this->pageBuilderService->getPageData($page);
        $data['plugin'] = plugin('PageBuilder');

        return view('PageBuilder::design.index', $data);
    }

    /**
     * 预览模块HTML
     *
     * @param  Request  $request
     * @param  string|null  $page  页面标识，null表示首页
     * @return string
     * @throws Exception
     */
    public function previewModule(Request $request, ?string $page = null): string
    {
        $module = json_decode($request->getContent(), true);
        $design = (bool) $request->get('design');

        $moduleId   = $module['module_id'] ?? '';
        $moduleCode = $module['code'] ?? '';
        $content    = $module['content'] ?? '';
        $viewPath   = $module['view_path'] ?? '';

        if (empty($viewPath)) {
            $viewPath = "PageBuilder::front.modules.{$moduleCode}";
        }

        // 使用 DesignService 统一处理模块数据，确保与前台页面一致
        $processedContent = DesignService::getInstance()->handleModuleContent($moduleCode, $content);

        $viewData = [
            'code'      => $moduleCode,
            'module_id' => $moduleId,
            'view_path' => $viewPath,
            'content'   => $processedContent,
            'design'    => $design,
        ];

        // 返回完整的section HTML，包括编辑按钮
        return view('PageBuilder::front.partials.module-section', [
            'module'    => $module,
            'content'   => $viewData['content'],
            'module_id' => $viewData['module_id'],
            'code'      => $viewData['code'],
        ])->render();
    }

    /**
     * 保存页面模块数据
     *
     * @param  Request  $request
     * @param  string|null  $page  页面标识，null表示首页
     * @return JsonResponse
     */
    public function update(Request $request, ?string $page = null): JsonResponse
    {
        try {
            $modules = $request->input('modules', []);
            $this->pageBuilderService->savePageModules($modules, $page);

            return json_success('保存成功');
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * 导入演示数据
     *
     * @param  string|null  $page  页面标识，null表示首页
     * @return JsonResponse
     */
    public function importDemo(?string $page = null): JsonResponse
    {
        try {
            $moduleData = $this->pageBuilderService->importDemoData($page);

            return json_success('演示数据导入成功', $moduleData);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
