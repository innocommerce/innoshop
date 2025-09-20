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
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\OptionValue;
use InnoShop\Common\Repositories\OptionRepo;
use InnoShop\Common\Repositories\OptionValueRepo;
use InnoShop\Panel\Requests\OptionValueRequest;

class OptionValueController extends BaseController
{
    protected $model = OptionValue::class;

    protected $repo = OptionValueRepo::class;

    /**
     * Display option values list page
     *
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        // 获取选项值数据
        $optionValues = OptionValueRepo::getInstance()->list($filters);

        // 获取所有选项组用于筛选下拉框
        $allOptionGroups = OptionRepo::getInstance()->all(['active' => 1]);

        $data = [
            'optionValues'    => $optionValues, // 选项值数据
            'allOptionGroups' => $allOptionGroups,
        ];

        return inno_view('panel::option_values.index', $data);
    }

    /**
     * Store new option value
     *
     * @param  OptionValueRequest  $request
     * @return mixed
     * @throws Exception
     */
    public function store(OptionValueRequest $request): mixed
    {
        try {
            $data        = $request->all();
            $optionValue = OptionValueRepo::getInstance()->create($data);

            if ($request->ajax()) {
                return json_success(panel_trans('common.created_success'), $optionValue);
            }

            return redirect(panel_route('option_values.index'))
                ->with('instance', $optionValue)
                ->with('success', panel_trans('common.created_success'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return json_fail($e->getMessage());
            }

            return redirect(panel_route('option_values.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show option value details (for AJAX requests)
     *
     * @param  OptionValue  $optionValue
     * @return mixed
     * @throws Exception
     */
    public function show(OptionValue $optionValue): mixed
    {
        $data = [
            'id'        => $optionValue->id,
            'option_id' => $optionValue->option_id,
            'image'     => $optionValue->image,
            'position'  => $optionValue->position,
            'active'    => $optionValue->active,
            'name'      => $optionValue->name ?? [],
        ];

        return response()->json($data);
    }

    /**
     * Update existing option value
     *
     * @param  OptionValueRequest  $request
     * @param  OptionValue  $optionValue
     * @return mixed
     * @throws Exception
     */
    public function update(OptionValueRequest $request, OptionValue $optionValue): mixed
    {
        try {
            $data = $request->all();
            OptionValueRepo::getInstance()->update($optionValue, $data);

            if ($request->ajax()) {
                return json_success(panel_trans('common.updated_success'), $optionValue);
            }

            return redirect(panel_route('option_values.index'))
                ->with('instance', $optionValue)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            if ($request->ajax()) {
                return json_fail($e->getMessage());
            }

            return redirect(panel_route('option_values.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Delete option value
     *
     * @param  OptionValue  $optionValue
     * @return RedirectResponse
     * @throws Exception
     */
    public function destroy(OptionValue $optionValue): RedirectResponse
    {
        try {
            OptionValueRepo::getInstance()->destroy($optionValue);

            return redirect(panel_route('option_values.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('option_values.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
