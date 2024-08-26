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
use InnoShop\Common\Models\Catalog;
use InnoShop\Common\Repositories\CatalogRepo;
use InnoShop\Common\Resources\CatalogSimple;
use InnoShop\Panel\Requests\CatalogRequest;

class CatalogController extends BaseController
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
            'catalogs' => CatalogRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::catalogs.index', $data);
    }

    /**
     * Catalog creation page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(): mixed
    {
        return $this->form(new Catalog);
    }

    /**
     * @param  CatalogRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(CatalogRequest $request): RedirectResponse
    {
        try {
            $data    = $request->all();
            $catalog = CatalogRepo::getInstance()->create($data);

            return redirect(panel_route('catalogs.index'))
                ->with('instance', $catalog)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('catalogs.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Catalog  $catalog
     * @return mixed
     * @throws \Exception
     */
    public function edit(Catalog $catalog): mixed
    {
        return $this->form($catalog);
    }

    /**
     * @param  $catalog
     * @return mixed
     * @throws \Exception
     */
    public function form($catalog): mixed
    {
        $catalogs = CatalogSimple::collection(CatalogRepo::getInstance()->all(['active' => 1]))->jsonSerialize();
        $data     = [
            'catalog'  => $catalog,
            'catalogs' => $catalogs,
        ];

        return inno_view('panel::catalogs.form', $data);
    }

    /**
     * @param  CatalogRequest  $request
     * @param  Catalog  $catalog
     * @return RedirectResponse
     */
    public function update(CatalogRequest $request, Catalog $catalog): RedirectResponse
    {
        try {
            $data = $request->all();
            CatalogRepo::getInstance()->update($catalog, $data);

            return redirect(panel_route('catalogs.index'))
                ->with('instance', $catalog)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('catalogs.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Catalog  $catalog
     * @return RedirectResponse
     */
    public function destroy(Catalog $catalog): RedirectResponse
    {
        try {
            CatalogRepo::getInstance()->destroy($catalog);

            return redirect(panel_route('catalogs.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('catalogs.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
