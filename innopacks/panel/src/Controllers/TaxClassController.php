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
use InnoShop\Common\Models\TaxClass;
use InnoShop\Common\Repositories\AddressRepo;
use InnoShop\Common\Repositories\TaxClassRepo;
use InnoShop\Common\Repositories\TaxRateRepo;
use InnoShop\Panel\Requests\TaxClassRequest;
use Throwable;

class TaxClassController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters  = $request->all();
        $taxRates = TaxRateRepo::getInstance()->all();

        $data = [
            'tax_classes'   => TaxClassRepo::getInstance()->list($filters),
            'tax_rates'     => $taxRates,
            'address_types' => AddressRepo::getAddressTypes(),
        ];

        return inno_view('panel::tax_classes.index', $data);
    }

    /**
     * @param  TaxClass  $taxClass
     * @return mixed
     * @throws Exception
     */
    public function show(TaxClass $taxClass): mixed
    {
        $taxClass->load(['taxRules']);

        return $taxClass;
    }

    /**
     * TaxClass creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new TaxClass);
    }

    /**
     * @param  TaxClassRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(TaxClassRequest $request): RedirectResponse
    {
        try {
            $data     = $request->all();
            $taxClass = TaxClassRepo::getInstance()->create($data);

            return redirect(panel_route('tax_classes.index'))
                ->with('instance', $taxClass)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('tax_classes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  TaxClass  $taxClass
     * @return mixed
     * @throws Exception
     */
    public function edit(TaxClass $taxClass): mixed
    {
        return $this->form($taxClass);
    }

    /**
     * @param  TaxClass  $taxClass
     * @return mixed
     */
    public function form(TaxClass $taxClass): mixed
    {
        $taxRates = TaxRateRepo::getInstance()->all();
        $taxRules = $taxClass->taxRules()->get();
        $data     = [
            'tax_class'     => $taxClass,
            'tax_rates'     => $taxRates,
            'tax_rules'     => $taxRules,
            'address_types' => AddressRepo::getAddressTypes(),
        ];

        return inno_view('panel::tax_classes.form', $data);
    }

    /**
     * @param  TaxClassRequest  $request
     * @param  TaxClass  $taxClass
     * @return RedirectResponse
     */
    public function update(TaxClassRequest $request, TaxClass $taxClass): RedirectResponse
    {
        try {
            $data = $request->all();
            TaxClassRepo::getInstance()->update($taxClass, $data);

            return redirect(panel_route('tax_classes.index'))
                ->with('instance', $taxClass)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('tax_classes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  TaxClass  $taxClass
     * @return RedirectResponse
     */
    public function destroy(TaxClass $taxClass): RedirectResponse
    {
        try {
            TaxClassRepo::getInstance()->destroy($taxClass);

            return redirect(panel_route('tax_classes.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('tax_classes.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
