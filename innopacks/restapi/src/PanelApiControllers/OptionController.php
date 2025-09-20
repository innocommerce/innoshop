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
use InnoShop\Common\Repositories\OptionRepo;
use InnoShop\Common\Repositories\OptionValueRepo;

class OptionController extends BaseController
{
    /**
     * 获取可用的选项列表
     *
     * @param  Request  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function available(Request $request): JsonResponse
    {
        $filters           = $request->all();
        $filters['active'] = 1; // 只获取激活的选项

        $options = OptionRepo::getInstance()->list($filters);

        // 格式化数据，包含选项值数量
        $formattedOptions = [];
        foreach ($options as $option) {
            $formattedOptions[] = [
                'id'                  => $option->id,
                'name'                => $option->getLocalizedName(),
                'description'         => $option->getLocalizedDescription(),
                'type'                => $option->type,
                'sort_order'          => $option->sort_order,
                'active'              => $option->active,
                'option_values_count' => $option->optionValues->count(),
                'created_at'          => $option->created_at,
                'updated_at'          => $option->updated_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => ['options' => $formattedOptions],
        ]);
    }

    /**
     * 根据选项ID获取选项值列表
     *
     * @param  Request  $request
     * @param  int  $optionId
     * @return JsonResponse
     * @throws Exception
     */
    public function values(Request $request, int $optionId): JsonResponse
    {
        $filters              = $request->all();
        $filters['option_id'] = $optionId;
        $filters['active']    = 1; // 只获取激活的选项值

        $optionValues = OptionValueRepo::getInstance()->list($filters);

        // 格式化数据
        $formattedValues = [];
        foreach ($optionValues as $optionValue) {
            $formattedValues[] = [
                'id'         => $optionValue->id,
                'option_id'  => $optionValue->option_id,
                'name'       => $optionValue->getLocalizedName(),
                'image'      => $optionValue->image,
                'image_url'  => $optionValue->image ? $optionValue->getImageUrl() : null,
                'sort_order' => $optionValue->sort_order,
                'active'     => $optionValue->active,
                'created_at' => $optionValue->created_at,
                'updated_at' => $optionValue->updated_at,
            ];
        }

        return response()->json([
            'success' => true,
            'data'    => ['option_values' => $formattedValues],
        ]);
    }
}
