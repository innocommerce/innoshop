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
use InnoShop\Common\Models\PageModule;
use InnoShop\Common\Repositories\PageRepo;
use Plugin\PageBuilder\Services\DesignService;

class PageController extends Controller
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function show(Request $request): mixed
    {
        $locale = front_locale_code();
        $slug = hide_url_locale() 
            ? trim($request->path(), '/')
            : trim(str_replace("$locale/", '', $request->path()), '/');

        $page = PageRepo::getInstance()
            ->builder(['slug' => $slug, 'active' => true])
            ->firstOrFail();
        $page->increment('viewed');

        $pageModule = PageModule::query()->where('page_id', $page->id)->first();
        $modules = $pageModule->module_data ?? [];

        $processedModules = [];
        foreach ($modules as $module) {
            $moduleCode = $module['code'] ?? '';
            $content = $module['content'] ?? [];

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
