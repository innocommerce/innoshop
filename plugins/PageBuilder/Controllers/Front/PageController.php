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
        if (hide_url_locale()) {
            $slug = trim($request->getRequestUri(), '/');
        } else {
            $slug = str_replace("/$locale/", '', $request->getRequestUri());
        }

        $filters = [
            'slug'   => $slug,
            'active' => true,
        ];
        $page = PageRepo::getInstance()->builder($filters)->firstOrFail();
        $page->increment('viewed');

        $pageModule = PageModule::query()->where('page_id', $page->id)->first();

        $modules['modules'] = $pageModule->module_data ?? [];

        // 使用 DesignService 处理每个模块，确保与预览一致
        $processedModules = [];
        foreach ($modules['modules'] as $module) {
            $moduleCode = $module['code'] ?? '';
            $content    = $module['content'] ?? [];

            if ($moduleCode && $content) {
                $processedContent = DesignService::getInstance()->handleModuleContent($moduleCode, $content);

                $processedModules[] = [
                    'code'      => $moduleCode,
                    'content'   => $processedContent,
                    'module_id' => $module['module_id'] ?? 'module-'.uniqid(),
                    'name'      => $module['name'] ?? '',
                    'view_path' => $module['view_path'] ?? '',
                ];
            }
        }

        $data = [
            'page'    => $page,
            'modules' => $processedModules,
        ];

        return view('WebBuilder::front.page', $data);
    }
}
