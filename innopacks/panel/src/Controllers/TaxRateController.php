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
use InnoShop\Common\Models\TaxRate;
use InnoShop\Common\Repositories\RegionRepo;
use InnoShop\Common\Repositories\TaxRateRepo;
use InnoShop\Panel\Requests\TaxRateRequest;

class TaxRateController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'types'     => [['value' => 'percent', 'label' => '百分比'], ['value' => 'fixed', 'label' => '固定']],
            'regions'   => RegionRepo::getInstance()->all()->toArray(),
            'tax_rates' => TaxRateRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::tax_rates.index', $data);
    }

    /**
     * @param  TaxRate  $taxRate
     * @return mixed
     */
    public function show(TaxRate $taxRate): mixed
    {
        $taxRate->load(['region']);

        return $taxRate;
    }

    /**
     * TaxRate creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new TaxRate);
    }

    /**
     * @param  TaxRateRequest  $request
     * @return JsonResponse
     */
    public function store(TaxRateRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            TaxRateRepo::getInstance()->create($data);

            return json_success(panel_trans('common.created_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  TaxRate  $taxRate
     * @return mixed
     * @throws Exception
     */
    public function edit(TaxRate $taxRate): mixed
    {
        return $this->form($taxRate);
    }

    /**
     * @param  $taxRate
     * @return mixed
     * @throws Exception
     */
    public function form($taxRate): mixed
    {
        $data = [
            'tax_rate' => $taxRate,
            'types'    => [['value' => 'percent', 'label' => '百分比'], ['value' => 'fixed', 'label' => '固定']],
            'regions'  => RegionRepo::getInstance()->all()->toArray(),
        ];

        return inno_view('panel::tax_rates.form', $data);
    }

    /**
     * @param  TaxRateRequest  $request
     * @param  TaxRate  $taxRate
     * @return JsonResponse
     */
    public function update(TaxRateRequest $request, TaxRate $taxRate): JsonResponse
    {
        try {
            $data = $request->all();
            TaxRateRepo::getInstance()->update($taxRate, $data);

            return json_success(panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  TaxRate  $taxRate
     * @return RedirectResponse
     */
    public function destroy(TaxRate $taxRate): RedirectResponse
    {
        try {
            TaxRateRepo::getInstance()->destroy($taxRate);

            return redirect(panel_route('tax_rates.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('tax_rates.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
