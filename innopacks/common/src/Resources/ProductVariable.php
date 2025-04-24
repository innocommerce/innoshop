<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Resources;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariable extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array
     * @throws Exception
     */
    public function toArray(Request $request): array
    {
        $names = $this['name'];
        foreach ($names as $locale => $name) {
            if (empty($name)) {
                $names[$locale] = $names[setting_locale_code()];
            }
        }

        $values = $this['values'];
        foreach ($values as $index => $value) {
            $valueNames = $value['name'];
            foreach ($valueNames as $locale => $valueName) {
                if (empty($valueName)) {
                    $values[$index]['name'][$locale] = $valueNames[setting_locale_code()];
                }
            }
        }

        $data = [
            'name'   => $names,
            'values' => $values,
        ];

        return fire_hook_filter('resource.product.variable', $data);
    }
}
