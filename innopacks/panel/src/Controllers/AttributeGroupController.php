<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Models\Attribute\Group;
use InnoShop\Common\Repositories\Attribute\GroupRepo;

class AttributeGroupController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request): mixed
    {
        $data = [
            'attributes' => Attribute\Group::query()->with([
                'translations',
            ])->paginate(),
        ];

        return inno_view('panel::attribute_groups.index', $data);
    }

    /**
     * @param  Group  $attributeGroup
     * @return Group
     */
    public function show(Attribute\Group $attributeGroup): Group
    {
        return $attributeGroup->load(['translations']);
    }

    /**
     * @param  Request  $request
     * @param  Group  $attributeGroup
     * @return JsonResponse
     * @throws \Exception
     */
    public function update(Request $request, Attribute\Group $attributeGroup): JsonResponse
    {
        GroupRepo::getInstance()->update($attributeGroup, $request->all());

        return json_success(trans('common.updated_success'));
    }

    /**
     * @param  Group  $attributeGroup
     * @return mixed
     */
    public function destroy(Attribute\Group $attributeGroup): mixed
    {
        $attributeGroup->delete();

        return redirect(panel_route('attribute_groups.index'))
            ->with('success', panel_trans('common.deleted_success'));
    }
}
