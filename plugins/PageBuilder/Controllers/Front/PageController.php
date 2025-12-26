<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\PageBuilder\Controllers\Front;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Models\PageModule;
use InnoShop\Common\Repositories\PageRepo;
use Plugin\PageBuilder\Services\DesignService;

class PageController extends Controller
{
    /**
     * Page list (if needed in the future)
     *
     * @return mixed
     */
    public function index(): mixed
    {
        // Redirect to home or implement page list if needed
        return redirect()->route('front.home.index');
    }

    /**
     * Show page by ID
     *
     * @param  Page  $page
     * @return mixed
     * @throws Exception
     */
    public function show(Page $page): mixed
    {
        if (! $page->active) {
            abort(404);
        }

        return $this->renderPage($page);
    }

    /**
     * Show page by slug (consistent with product-{slug}, category-{slug}, article-{slug})
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function slugShow(Request $request): mixed
    {
        $slug = $request->slug;
        $page = PageRepo::getInstance()
            ->builder(['slug' => $slug, 'active' => true])
            ->firstOrFail();

        return $this->renderPage($page);
    }

    /**
     * Render page with modules
     *
     * @param  Page  $page
     * @return mixed
     * @throws Exception
     */
    private function renderPage(Page $page): mixed
    {
        $page->increment('viewed');

        $pageModule = PageModule::query()->where('page_id', $page->id)->first();
        $modules    = $pageModule->module_data ?? [];

        $processedModules = [];
        foreach ($modules as $module) {
            $moduleCode = $module['code'] ?? '';
            $content    = $module['content'] ?? [];

            if ($moduleCode && $content) {
                $processedModules[] = [
                    'code'      => $moduleCode,
                    'content'   => DesignService::getInstance()->handleModuleContent($moduleCode, $content),
                    'module_id' => $module['module_id'] ?? 'module-'.uniqid(),
                    'name'      => $module['name'] ?? '',
                    'view_path' => $module['view_path'] ?? '',
                ];
            }
        }

        return view('PageBuilder::front.page', [
            'page'    => $page,
            'modules' => $processedModules,
        ]);
    }
}
