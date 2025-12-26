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
     * Page editor main page - unified handling for home page and single pages
     *
     * @param  string|null  $page  Page identifier, 'home' for home page, others are page ID or slug, null means home page
     * @return mixed
     * @throws Exception
     */
    public function index(?string $page = null): mixed
    {
        $data           = $this->pageBuilderService->getPageData($page);
        $data['plugin'] = plugin('PageBuilder');

        // Get page model for preview URL generation
        if ($page && $page !== 'home') {
            $data['pageModel'] = $this->pageBuilderService->findPage($page);
        } else {
            $data['pageModel'] = null;
        }

        return view('PageBuilder::design.index', $data);
    }

    /**
     * Preview module HTML
     *
     * @param  Request  $request
     * @param  string|null  $page  Page identifier, null means home page
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

        $processedContent = DesignService::getInstance()->handleModuleContent($moduleCode, $content);

        $viewData = [
            'code'      => $moduleCode,
            'module_id' => $moduleId,
            'view_path' => $viewPath,
            'content'   => $processedContent,
            'design'    => $design,
        ];

        return view('PageBuilder::front.partials.module-section', [
            'module'    => $module,
            'content'   => $viewData['content'],
            'module_id' => $viewData['module_id'],
            'code'      => $viewData['code'],
        ])->render();
    }

    /**
     * Save page module data
     *
     * @param  Request  $request
     * @param  string|null  $page  Page identifier, null means home page
     * @return JsonResponse
     */
    public function update(Request $request, ?string $page = null): JsonResponse
    {
        try {
            $modules = $request->input('modules', []);
            $this->pageBuilderService->savePageModules($modules, $page);

            return json_success(trans('PageBuilder::common.saved_successfully'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Import demo data
     *
     * @param  string|null  $page  Page identifier, null means home page
     * @return JsonResponse
     */
    public function importDemo(?string $page = null): JsonResponse
    {
        try {
            $moduleData = $this->pageBuilderService->importDemoData($page);

            return json_success(trans('PageBuilder::common.demo_imported_successfully'), $moduleData);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
