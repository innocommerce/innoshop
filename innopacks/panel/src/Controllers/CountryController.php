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
use InnoShop\Common\Models\Country;
use InnoShop\Common\Repositories\CountryRepo;
use InnoShop\Panel\Requests\CountryRequest;
use Throwable;

class CountryController extends BaseController
{
    protected string $modelClass = Country::class;

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'countries' => CountryRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::countries.index', $data);
    }

    /**
     * @param  Country  $country
     * @return Country
     */
    public function show(Country $country): Country
    {
        return $country;
    }

    /**
     * Country creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Country);
    }

    /**
     * @param  CountryRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(CountryRequest $request): RedirectResponse
    {
        try {
            $data    = $request->all();
            $country = CountryRepo::getInstance()->create($data);

            return redirect(panel_route('countries.index'))
                ->with('instance', $country)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('countries.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Country  $country
     * @return mixed
     * @throws Exception
     */
    public function edit(Country $country): mixed
    {
        return $this->form($country);
    }

    /**
     * @param  $country
     * @return mixed
     * @throws Exception
     */
    public function form($country): mixed
    {
        $data = [
            'country' => $country,
        ];

        return inno_view('panel::countries.form', $data);
    }

    /**
     * @param  CountryRequest  $request
     * @param  Country  $country
     * @return RedirectResponse
     */
    public function update(CountryRequest $request, Country $country): RedirectResponse
    {
        try {
            $data = $request->all();
            CountryRepo::getInstance()->update($country, $data);

            return redirect(panel_route('countries.index'))
                ->with('instance', $country)
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('countries.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Country  $country
     * @return RedirectResponse
     */
    public function destroy(Country $country): RedirectResponse
    {
        try {
            CountryRepo::getInstance()->destroy($country);

            return redirect(panel_route('countries.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('countries.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
