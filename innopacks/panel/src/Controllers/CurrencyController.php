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
use InnoShop\Common\Models\Currency;
use InnoShop\Common\Repositories\CurrencyRepo;
use InnoShop\Panel\Requests\CurrencyRequest;
use Throwable;

class CurrencyController extends BaseController
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
            'currencies' => CurrencyRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::currencies.index', $data);
    }

    /**
     * @param  Currency  $currency
     * @return Currency
     */
    public function show(Currency $currency): Currency
    {
        return $currency;
    }

    /**
     * Currency creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Currency);
    }

    /**
     * @param  CurrencyRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(CurrencyRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            CurrencyRepo::getInstance()->create($data);

            return json_success(panel_trans('common.saved_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Currency  $currency
     * @return mixed
     * @throws Exception
     */
    public function edit(Currency $currency): mixed
    {
        return $this->form($currency);
    }

    /**
     * @param  $currency
     * @return mixed
     * @throws Exception
     */
    public function form($currency): mixed
    {
        $data = [
            'currency' => $currency,
        ];

        return inno_view('panel::currencies.form', $data);
    }

    /**
     * @param  CurrencyRequest  $request
     * @param  Currency  $currency
     * @return JsonResponse
     */
    public function update(CurrencyRequest $request, Currency $currency): JsonResponse
    {
        try {
            $data = $request->all();
            CurrencyRepo::getInstance()->update($currency, $data);

            return json_success(panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Currency  $currency
     * @return RedirectResponse
     */
    public function destroy(Currency $currency): RedirectResponse
    {
        try {
            if ($currency->code == system_setting('currency')) {
                throw new \Exception('Cannot delete default currency');
            }
            CurrencyRepo::getInstance()->destroy($currency);

            return redirect(panel_route('currencies.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('currencies.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
