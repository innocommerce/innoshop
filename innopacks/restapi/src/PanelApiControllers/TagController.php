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
use InnoShop\Common\Models\Tag;
use InnoShop\Common\Repositories\TagRepo;
use InnoShop\Common\Resources\TagListItem;
use InnoShop\Common\Resources\TagSimple;
use InnoShop\Panel\Requests\TagRequest;
use Knuckles\Scribe\Attributes\Endpoint;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\QueryParam;
use Knuckles\Scribe\Attributes\UrlParam;
use Throwable;

#[Group('Panel - Tags')]
class TagController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    #[Endpoint('List tags')]
    #[QueryParam('tag_ids', 'string', required: false, description: 'Comma-separated tag IDs')]
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
     * @param  Request  $request
     * @return AnonymousResourceCollection
     * @throws Exception
     */
    #[Endpoint('Get tags by IDs')]
    #[QueryParam('ids', 'string', required: true)]
    public function names(Request $request): AnonymousResourceCollection
    {
        $tags = TagRepo::getInstance()->getListByTagIDs($request->get('ids'));

        return TagSimple::collection($tags);
    }

    /**
     * @param  TagRequest  $request
     * @return mixed
     * @throws Throwable
     */
    #[Endpoint('Create tag')]
    public function store(TagRequest $request): mixed
    {
        try {
            $data = $request->all();
            $tag  = TagRepo::getInstance()->create($data);

            return json_success(common_trans('base.updated_success'), $tag);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  TagRequest  $request
     * @param  Tag  $tag
     * @return mixed
     */
    #[Endpoint('Update tag')]
    #[UrlParam('tag', 'integer', description: 'Tag ID')]
    public function update(TagRequest $request, Tag $tag): mixed
    {
        try {
            $data = $request->all();
            TagRepo::getInstance()->update($tag, $data);

            return json_success(common_trans('base.updated_success'), $tag);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Partial update a tag.
     * PATCH /api/panel/tags/{tag}
     *
     * @param  TagRequest  $request
     * @param  Tag  $tag
     * @return mixed
     */
    #[Endpoint('Partial update tag')]
    #[UrlParam('tag', 'integer', description: 'Tag ID')]
    public function patch(TagRequest $request, Tag $tag): mixed
    {
        try {
            $data = $request->validated();
            TagRepo::getInstance()->patch($tag, $data);

            return update_json_success($tag);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Tag  $tag
     * @return mixed
     */
    #[Endpoint('Delete tag')]
    #[UrlParam('tag', 'integer', description: 'Tag ID')]
    public function destroy(Tag $tag): mixed
    {
        try {
            TagRepo::getInstance()->destroy($tag);

            return json_success(common_trans('base.deleted_success'));
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
    #[Endpoint('Autocomplete tags')]
    #[QueryParam('keyword', 'string', required: false)]
    public function autocomplete(Request $request): AnonymousResourceCollection
    {
        $name     = $request->get('keyword');
        $catalogs = TagRepo::getInstance()->searchByName($name);

        return TagSimple::collection($catalogs);
    }
}
