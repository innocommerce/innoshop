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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Option;
use InnoShop\Common\Repositories\OptionRepo;
use InnoShop\Common\Repositories\OptionValueRepo;
use InnoShop\Panel\Requests\OptionRequest;

class OptionController extends BaseController
{
    protected string $modelClass = Option::class;

    /**
     * Display options list page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters       = $request->all();
        $option_groups = OptionRepo::getInstance()->all($filters);

        // 选项组数据已经包含JSON格式的多语言字段，无需额外加载
        $data = [
            'option_groups'      => $option_groups,
            'option_groups_data' => $option_groups, // 用于JavaScript
        ];

        return inno_view('panel::options.index', $data);
    }

    /**
     * Store new option
     *
     * @param  OptionRequest  $request
     * @return RedirectResponse|JsonResponse
     * @throws Exception
     */
    public function store(OptionRequest $request): RedirectResponse|JsonResponse
    {
        try {
            $data   = $request->all();
            $option = OptionRepo::getInstance()->create($data);

            // 如果是AJAX请求，返回JSON响应
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => panel_trans('common.created_success'),
                    'data'    => $option,
                ]);
            }

            return redirect(panel_route('options.index'))
                ->with('instance', $option)
                ->with('success', panel_trans('common.created_success'));
        } catch (Exception $e) {
            // 如果是AJAX请求，返回JSON错误响应
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect(panel_route('options.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Update existing option
     *
     * @param  OptionRequest  $request
     * @param  Option  $option
     * @return RedirectResponse|JsonResponse
     * @throws Exception
     */
    public function update(OptionRequest $request, Option $option): RedirectResponse|JsonResponse
    {
        try {
            $data = $request->all();
            OptionRepo::getInstance()->update($option, $data);

            // 如果是AJAX请求，返回JSON响应
            if ($request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => panel_trans('common.updated_success'),
                    'data'    => $option,
                ]);
            }

            return redirect(panel_route('options.index'))
                ->with('instance', $option)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            // 如果是AJAX请求，返回JSON错误响应
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return redirect(panel_route('options.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get option data for editing (AJAX endpoint)
     *
     * @param  Option  $option
     * @return JsonResponse
     * @throws Exception
     */
    public function show(Option $option): JsonResponse
    {
        try {
            // 加载选项组的翻译数据
            $option->load('translations');

            return response()->json([
                'success' => true,
                'data'    => $option,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Delete option
     *
     * @param  Option  $option
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(Option $option): RedirectResponse
    {
        try {
            OptionRepo::getInstance()->destroy($option);

            return redirect(panel_route('options.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('options.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

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
    public function valuesByOptionId(Request $request, int $optionId): JsonResponse
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
