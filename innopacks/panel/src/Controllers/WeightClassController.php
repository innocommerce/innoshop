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
use InnoShop\Common\Models\WeightClass;
use InnoShop\Common\Repositories\WeightClassRepo;
use InnoShop\Panel\Requests\WeightClassRequest;

class WeightClassController extends BaseController
{
    protected string $modelClass = WeightClass::class;

    /**
     * Display a listing of weight classes.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function index(Request $request): mixed
    {
        $filters       = $request->all();
        $weightClasses = WeightClassRepo::getInstance()->builder($filters)->get();

        return inno_view('panel::weight_classes.index', compact('weightClasses'));
    }

    /**
     * Show the form for creating a new weight class.
     *
     * @return mixed
     */
    public function create(): mixed
    {
        return $this->form(new WeightClass);
    }

    /**
     * Store a newly created weight class in storage.
     *
     * @param  WeightClassRequest  $request
     * @return RedirectResponse
     */
    public function store(WeightClassRequest $request): RedirectResponse
    {
        try {
            $data = $request->all();
            WeightClassRepo::getInstance()->create($data);

            return redirect(panel_route('weight_classes.index'))
                ->with('success', panel_trans('common.saved_success'));
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show the form for editing the specified weight class.
     *
     * @param  WeightClass  $weight_class
     * @return mixed
     */
    public function edit(WeightClass $weight_class): mixed
    {
        return $this->form($weight_class);
    }

    /**
     * Update the specified weight class in storage.
     *
     * @param  WeightClassRequest  $request
     * @param  WeightClass  $weight_class
     * @return RedirectResponse
     */
    public function update(WeightClassRequest $request, WeightClass $weight_class): RedirectResponse
    {
        try {
            $data = $request->all();
            WeightClassRepo::getInstance()->update($weight_class, $data);

            return redirect(panel_route('weight_classes.index'))
                ->with('success', panel_trans('common.updated_success'));
        } catch (Exception $e) {
            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Remove the specified weight class from storage.
     *
     * @param  WeightClass  $weight_class
     * @return RedirectResponse
     */
    public function destroy(WeightClass $weight_class): RedirectResponse
    {
        try {
            // Prevent deletion of default weight class
            $defaultCode = system_setting('default_weight_class');
            if ($weight_class->code === $defaultCode) {
                return back()->withErrors(['error' => panel_trans('weight_class.error_cannot_delete_default')]);
            }

            // Check if weight class is being used by products
            $productsCount = $weight_class->products()->count();
            if ($productsCount > 0) {
                return back()->withErrors(['error' => panel_trans('weight_class.error_in_use')]);
            }

            WeightClassRepo::getInstance()->destroy($weight_class);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display the form.
     *
     * @param  WeightClass  $weight_class
     * @return mixed
     */
    private function form(WeightClass $weight_class): mixed
    {
        return inno_view('panel::weight_classes.form', compact('weight_class'));
    }
}
