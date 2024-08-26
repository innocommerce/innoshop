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
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InnoShop\Common\Models\Tag;
use InnoShop\Common\Repositories\TagRepo;
use InnoShop\Common\Resources\TagListItem;
use InnoShop\Common\Resources\TagSimple;
use InnoShop\Panel\Requests\TagRequest;
use Throwable;

class TagController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        if (isset($filters['tag_ids'])) {
            $tagIds             = explode(',', $filters['tag_ids']);
            $filters['tag_ids'] = $tagIds;
        }

        $catalogs = TagRepo::getInstance()->builder($filters)->limit(10)->get();

        return TagListItem::collection($catalogs);
    }

    /**
     * @param  TagRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(TagRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            $tag  = TagRepo::getInstance()->create($data);

            return json_success(panel_trans('common.updated_success'), $tag);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  TagRequest  $request
     * @param  Tag  $tag
     * @return JsonResponse
     */
    public function update(TagRequest $request, Tag $tag): JsonResponse
    {
        try {
            $data = $request->all();
            TagRepo::getInstance()->update($tag, $data);

            return json_success(panel_trans('common.updated_success'), $tag);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Tag  $tag
     * @return JsonResponse
     */
    public function destroy(Tag $tag): JsonResponse
    {
        try {
            TagRepo::getInstance()->destroy($tag);

            return json_success(panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Fuzzy search for auto complete.
     * /api/panel/tags/autocomplete?keyword=xxx
     *
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $name     = $request->get('keyword');
        $catalogs = TagRepo::getInstance()->searchByName($name);

        return TagSimple::collection($catalogs);
    }
}
