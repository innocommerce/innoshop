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
        $option_groups = OptionRepo::getInstance()->list($filters);

        $data = [
            'searchFields'  => OptionRepo::getSearchFieldOptions(),
            'filterButtons' => OptionRepo::getFilterButtonOptions(),
            'option_groups' => $option_groups,
        ];

        return inno_view('panel::options.index', $data);
    }

    /**
     * Option creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Option);
    }

    /**
     * @param  OptionRequest  $request
     * @return RedirectResponse
     * @throws Exception
     */
    public function store(OptionRequest $request): RedirectResponse
    {
        try {
            $data   = $request->all();
            $option = OptionRepo::getInstance()->create($data);

            // Create option values for new options
            $valuesRaw = $request->input('values', '');
            $values    = is_string($valuesRaw) && $valuesRaw !== '' ? json_decode($valuesRaw, true) : (array) $valuesRaw;
            if (! empty($values) && is_array($values) && $option->id) {
                foreach ($values as $valueItem) {
                    OptionValueRepo::getInstance()->create([
                        'option_id' => $option->id,
                        'name'      => $valueItem['name'] ?? [],
                        'image'     => $valueItem['image'] ?? '',
                        'active'    => 1,
                    ]);
                }
            }

            return redirect(panel_route('options.index'))
                ->with('instance', $option)
                ->with('success', panel_trans('common.created_success'));
        } catch (Exception $e) {
            return redirect(panel_route('options.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Option  $option
     * @return mixed
     * @throws Exception
     */
    public function edit(Option $option): mixed
    {
        return $this->form($option);
    }

    /**
     * @param  $option
     * @return mixed
     * @throws Exception
     */
    public function form($option): mixed
    {
        $valuesJson = $option->optionValues->map(function ($v) {
            return [
                'id'    => $v->id,
                'name'  => $v->name ?? [],
                'image' => $v->image ?? '',
            ];
        })->values()->toArray();

        $data = [
            'option'             => $option,
            'option_values_json' => $valuesJson,
        ];

        return inno_view('panel::options.form', $data);
    }

    /**
     * @param  OptionRequest  $request
     * @param  Option  $option
     * @return RedirectResponse
     * @throws Exception
     */
    public function update(OptionRequest $request, Option $option): RedirectResponse
    {
        try {
            $data = $request->all();
            OptionRepo::getInstance()->update($option, $data);

            return redirect(panel_route('options.index'))
                ->with('instance', $option)
                ->with('success', common_trans('base.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('options.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get option data (AJAX endpoint for product editing)
     *
     * @param  Option  $option
     * @return mixed
     * @throws Exception
     */
    public function show(Option $option): mixed
    {
        try {
            $option->load(['optionValues' => function ($query) {
                $query->orderBy('position')->orderBy('id');
            }]);

            return json_success('', $option);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
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
                ->with('success', common_trans('base.deleted_success'));
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
        $filters['active'] = 1;

        $options = OptionRepo::getInstance()->all($filters);

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
        $filters['active']    = 1;

        $optionValues = OptionValueRepo::getInstance()->all($filters);

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
