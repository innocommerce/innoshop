<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Models\Attribute\Group;
use InnoShop\Common\Repositories\Attribute\GroupRepo;
use Throwable;

class AttributeGroupController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $data = [
            'criteria'   => GroupRepo::getCriteria(),
            'attributes' => GroupRepo::getInstance()->list($request->all()),
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
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $attributeGroup = GroupRepo::getInstance()->create($request->all());

            return create_json_success($attributeGroup);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  Group  $attributeGroup
     * @return JsonResponse
     * @throws Exception
     */
    public function update(Request $request, Attribute\Group $attributeGroup): JsonResponse
    {
        try {
            $attributeGroup = GroupRepo::getInstance()->update($attributeGroup, $request->all());

            return update_json_success($attributeGroup);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Group  $attributeGroup
     * @return mixed
     */
    public function destroy(Attribute\Group $attributeGroup): mixed
    {
        $attributeGroup->translations()->delete();
        $attributeGroup->delete();

        return redirect(panel_route('attribute_groups.index'))
            ->with('success', panel_trans('common.deleted_success'));
    }
}
