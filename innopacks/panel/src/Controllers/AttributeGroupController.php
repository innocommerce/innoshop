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
use Illuminate\Http\Request;
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
        $panelLocale   = panel_locale_code();
        $allLocales    = locales();
        $defaultLocale = $allLocales->first(fn ($l) => $l->code === $panelLocale);

        $data = [
            'searchFields'      => GroupRepo::getSearchFieldOptions(),
            'filterButtons'     => GroupRepo::getFilterButtonOptions(),
            'attributes'        => GroupRepo::getInstance()->list($request->all()),
            'defaultLocaleName' => $defaultLocale->name ?? $panelLocale,
            'otherLocales'      => $allLocales->filter(fn ($l) => $l->code !== $panelLocale),
        ];

        return inno_view('panel::attribute_groups.index', $data);
    }

    /**
     * @param  Group  $attributeGroup
     * @return Group
     */
    public function show(Group $attributeGroup): mixed
    {
        $attributeGroup->load(['translations']);

        return json_success('', $attributeGroup);
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function store(Request $request): mixed
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
     * @return mixed
     * @throws Exception
     */
    public function update(Request $request, Group $attributeGroup): mixed
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
    public function destroy(Group $attributeGroup): mixed
    {
        try {
            $attributeGroup->translations()->delete();
            $attributeGroup->delete();

            return delete_json_success();
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
