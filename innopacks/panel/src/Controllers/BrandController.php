<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use InnoShop\Common\Models\Brand;
use InnoShop\Common\Repositories\BrandRepo;
use InnoShop\Panel\Requests\BrandRequest;

class BrandController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();
        $data    = [
            'brands' => BrandRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::brands.index', $data);
    }

    /**
     * Brand creation page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(): mixed
    {
        return $this->form(new Brand);
    }

    /**
     * @param  BrandRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(BrandRequest $request): RedirectResponse
    {
        try {
            $data  = $request->all();
            $brand = BrandRepo::getInstance()->create($data);

            return redirect(panel_route('brands.index'))
                ->with('instance', $brand)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('brands.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Brand  $brand
     * @return mixed
     * @throws \Exception
     */
    public function edit(Brand $brand): mixed
    {
        return $this->form($brand);
    }

    /**
     * @param  $brand
     * @return mixed
     * @throws \Exception
     */
    public function form($brand): mixed
    {
        $data = [
            'brand' => $brand,
        ];

        return inno_view('panel::brands.form', $data);
    }

    /**
     * @param  BrandRequest  $request
     * @param  Brand  $brand
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function update(BrandRequest $request, Brand $brand): RedirectResponse
    {
        try {
            $data = $request->all();
            BrandRepo::getInstance()->update($brand, $data);

            return redirect(panel_route('brands.index'))
                ->with('instance', $brand)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('brands.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Brand  $brand
     * @return RedirectResponse
     */
    public function destroy(Brand $brand): RedirectResponse
    {
        try {
            BrandRepo::getInstance()->destroy($brand);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
