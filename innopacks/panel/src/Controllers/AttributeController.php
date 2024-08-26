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
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Repositories\Attribute\GroupRepo;
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Panel\Requests\AttributeRequest;

class AttributeController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws \Exception
     */
    public function index(Request $request): mixed
    {
        $data = [
            'attributes' => Attribute::query()->with([
                'translations',
                'values.translations',
                'group.translations',
            ])->paginate(),
        ];

        return inno_view('panel::attributes.index', $data);
    }

    /**
     * Attribute creation page.
     *
     * @return mixed
     * @throws \Exception
     */
    public function create(): mixed
    {
        return $this->form(new Attribute);
    }

    /**
     * @param  AttributeRequest  $request
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function store(AttributeRequest $request): RedirectResponse
    {
        try {
            $data      = $request->all();
            $attribute = AttributeRepo::getInstance()->create($data);

            return redirect(panel_route('attributes.index'))
                ->with('instance', $attribute)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('attributes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Attribute  $attribute
     * @return mixed
     * @throws \Exception
     */
    public function edit(Attribute $attribute): mixed
    {
        return $this->form($attribute);
    }

    /**
     * @param  $attribute
     * @return mixed
     * @throws \Exception
     */
    public function form($attribute): mixed
    {
        $data = [
            'attribute'        => $attribute,
            'attribute_values' => $attribute->values->pluck('translations')->toArray(),
            'attribute_groups' => GroupRepo::getInstance()->getOptions(),
        ];

        return inno_view('panel::attributes.form', $data);
    }

    /**
     * @param  AttributeRequest  $request
     * @param  Attribute  $attribute
     * @return RedirectResponse
     * @throws \Throwable
     */
    public function update(AttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        try {
            $data = $request->all();
            AttributeRepo::getInstance()->update($attribute, $data);

            return redirect(panel_route('attributes.index'))
                ->with('instance', $attribute)
                ->with('success', panel_trans('common.updated_success'));
        } catch (\Exception $e) {
            return redirect(panel_route('attributes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Attribute  $attribute
     * @return RedirectResponse
     */
    public function destroy(Attribute $attribute): RedirectResponse
    {
        try {
            AttributeRepo::getInstance()->destroy($attribute);

            return back()->with('success', panel_trans('common.deleted_success'));
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
