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
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Page;
use InnoShop\Common\Repositories\PageRepo;
use InnoShop\Common\Resources\PageName;
use InnoShop\Common\Resources\PageSimple;
use InnoShop\Panel\Requests\PageRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Panel - Pages')]
class PageController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List pages')]
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $pages   = PageRepo::getInstance()->list($filters);

        return PageSimple::collection($pages);
    }

    /**
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get pages by IDs')]
    #[QueryParam('ids', 'string', required: true)]
    public function names(Request $request): AnonymousResourceCollection
    {
        $pages = PageRepo::getInstance()->getListByPageIDs($request->get('ids'));

        return PageName::collection($pages);
    }

    /**
     * @param  PageRequest  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create page')]
    public function store(PageRequest $request): mixed
    {
        try {
            $data = $request->all();
            $page = PageRepo::getInstance()->create($data);

            return json_success(common_trans('base.updated_success'), $page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  PageRequest  $request
     * @param  Page  $page
     * @return mixed
     */
    #[Endpoint('Update page')]
    #[UrlParam('page', 'integer', description: 'Page ID')]
    public function update(PageRequest $request, Page $page): mixed
    {
        try {
            $data = $request->all();
            PageRepo::getInstance()->update($page, $data);

            return json_success(common_trans('base.updated_success'), $page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update a page.
     * PATCH /api/panel/pages/{page}
     *
     * @param  PageRequest  $request
     * @param  Page  $page
     * @return mixed
     */
    #[Endpoint('Partial update page')]
    #[UrlParam('page', 'integer', description: 'Page ID')]
    public function patch(PageRequest $request, Page $page): mixed
    {
        try {
            $data = $request->validated();
            PageRepo::getInstance()->patch($page, $data);

            return update_json_success($page);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Page  $page
     * @return mixed
     */
    #[Endpoint('Delete page')]
    #[UrlParam('page', 'integer', description: 'Page ID')]
    public function destroy(Page $page): mixed
    {
        try {
            PageRepo::getInstance()->destroy($page);

            return json_success(common_trans('base.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/catalogs/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Autocomplete pages')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $catalogs = PageRepo::getInstance()->autocomplete($request->get('keyword') ?? '');

        return PageName::collection($catalogs);
    }
}
