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
use InnoShop\Common\Models\Region;
use InnoShop\Common\Repositories\CountryRepo;
use InnoShop\Common\Repositories\RegionRepo;
use InnoShop\Panel\Requests\RegionRequest;
use Throwable;

class RegionController extends BaseController
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
            'regions' => RegionRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::regions.index', $data);
    }

    /**
     * @param  Region  $region
     * @return Region
     */
    public function show(Region $region): Region
    {
        return $region->load(['regionStates']);
    }

    /**
     * Region creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Region);
    }

    /**
     * @param  RegionRequest  $request
     * @return JsonResponse
     * @throws Throwable
     */
    public function store(RegionRequest $request): JsonResponse
    {
        try {
            $data = $request->all();
            RegionRepo::getInstance()->create($data);

            return json_success(panel_trans('common.created_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Region  $region
     * @return mixed
     * @throws Exception
     */
    public function edit(Region $region): mixed
    {
        return $this->form($region);
    }

    /**
     * @param  $region
     * @return mixed
     * @throws Exception
     */
    public function form($region): mixed
    {
        $data = [
            'region'    => $region,
            'countries' => CountryRepo::getInstance()->builder()->get(),
        ];

        return inno_view('panel::regions.form', $data);
    }

    /**
     * @param  RegionRequest  $request
     * @param  Region  $region
     * @return JsonResponse
     */
    public function update(RegionRequest $request, Region $region): JsonResponse
    {
        try {
            $data = $request->all();
            RegionRepo::getInstance()->update($region, $data);

            return json_success(panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Region  $region
     * @return RedirectResponse
     */
    public function destroy(Region $region): RedirectResponse
    {
        try {
            RegionRepo::getInstance()->destroy($region);

            return redirect(panel_route('regions.index'))
                ->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return redirect(panel_route('regions.index'))
                ->withErrors(['error' => $e->getMessage()]);
        }
    }
}
