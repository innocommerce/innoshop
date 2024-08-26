<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Panel\Requests\PageRequest;
use Throwable;

class PageController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        return PageRepo::getInstance()->list($filters);
    }

    /**
     * @param  PageRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(PageRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            $page = PageRepo::getInstance()->create($data);

            return json_success(panel_trans('common.updated_success'), $page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  PageRequest  $request
     * @param  Page  $page
     * @return JsonResponse
     */
    public function update(PageRequest $request, Page $page): JsonResponse
    {
        try {
            $data = $request->all();
            PageRepo::getInstance()->update($page, $data);

            return json_success(panel_trans('common.updated_success'), $page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Page  $page
     * @return JsonResponse
     */
    public function destroy(Page $page): JsonResponse
    {
        try {
            PageRepo::getInstance()->destroy($page);

            return json_success(panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
