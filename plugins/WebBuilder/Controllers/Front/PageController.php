<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\WebBuilder\Controllers\Front;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use InnoShop\Common\Models\PageModule;
use InnoShop\Common\Repositories\PageRepo;
use Plugin\WebBuilder\Services\ModuleService;

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
        if (count(locales()) == 1) {
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

        $data = [
            'page'    => $page,
            'modules' => ModuleService::getInstance()->parseModules($modules['modules']),
        ];

        return view('WebBuilder::front.page', $data);

    }
}
