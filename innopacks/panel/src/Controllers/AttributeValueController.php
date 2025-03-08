<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\Request;
use InnoShop\Common\Models\Attribute\Value;
use InnoShop\Common\Repositories\Attribute\ValueRepo;

class AttributeValueController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function store(Request $request): mixed
    {
        try {
            $data   = $request->all();
            $values = $data['values'] ?? [];

            $attributeID = $data['attribute_id'] ?? 0;

            ValueRepo::getInstance()->createAttribute($attributeID, $values);

            return create_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  Value  $attributeValue
     * @return mixed
     */
    public function update(Request $request, Value $attributeValue): mixed
    {
        try {
            $data = $request->all();
            ValueRepo::getInstance()->updateTranslations($attributeValue, $data['values'] ?? []);

            return update_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Value  $attributeValue
     * @return mixed
     */
    public function destroy(Value $attributeValue): mixed
    {
        try {
            $attributeValue->translations()->delete();
            $attributeValue->delete();

            return delete_json_success();
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
