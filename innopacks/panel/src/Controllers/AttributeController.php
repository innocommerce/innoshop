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
use InnoShop\Common\Models\Attribute;
use InnoShop\Common\Repositories\Attribute\GroupRepo;
use InnoShop\Common\Repositories\AttributeRepo;
use InnoShop\Panel\Requests\AttributeRequest;
use Throwable;

class AttributeController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     * @throws Exception
     */
    public function index(Request $request): mixed
    {
        $filters = $request->all();

        $data = [
            'searchFields'  => AttributeRepo::getSearchFieldOptions(),
            'filterButtons' => AttributeRepo::getFilterButtonOptions(),
            'attributes'    => AttributeRepo::getInstance()->list($filters),
        ];

        return inno_view('panel::attributes.index', $data);
    }

    /**
     * Attribute creation page.
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        return $this->form(new Attribute);
    }

    /**
     * @param  AttributeRequest  $request
     * @return RedirectResponse
     * @throws Throwable
     */
    public function store(AttributeRequest $request): RedirectResponse
    {
        try {
            $data      = $request->all();
            $attribute = AttributeRepo::getInstance()->create($data);

            return redirect(panel_route('attributes.index'))
                ->with('instance', $attribute)
                ->with('success', common_trans('base.updated_success'));
        } catch (Exception $e) {
            return redirect(panel_route('attributes.index'))
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * @param  Attribute  $attribute
     * @return mixed
     * @throws Exception
     */
    public function edit(Attribute $attribute): mixed
    {
        return $this->form($attribute);
    }

    /**
     * @param  $attribute
     * @return mixed
     * @throws Exception
     */
    public function form($attribute): mixed
    {
        $valuesJson = $attribute->values->map(function ($v) {
            $names = [];
            foreach ($v->translations as $t) {
                $names[$t->locale] = $t->name;
            }

            return ['id' => $v->id, 'name' => $names];
        })->values()->toArray();

        $data = [
            'attribute'              => $attribute,
            'attribute_values_json'  => $valuesJson,
            'attribute_groups'       => GroupRepo::getInstance()->getOptions(),
            'attribute_type_options' => $this->getTypeOptions(),
        ];

        return inno_view('panel::attributes.form', $data);
    }

    /**
     * Value type options for the attribute form, labels translated for the
     * panel locale. Built here because block @php directives are unreliable
     * inside pushed blade sections.
     *
     * @return array
     */
    private function getTypeOptions(): array
    {
        $types = [
            Attribute::TYPE_SELECT   => 'attribute.type_select',
            Attribute::TYPE_INPUT    => 'attribute.type_input',
            Attribute::TYPE_NUMBER   => 'attribute.type_number',
            Attribute::TYPE_TEXTAREA => 'attribute.type_textarea',
            Attribute::TYPE_RICHTEXT => 'attribute.type_richtext',
            Attribute::TYPE_DATE     => 'attribute.type_date',
        ];

        $options = [];
        foreach ($types as $value => $key) {
            $options[] = ['value' => $value, 'label' => panel_trans($key)];
        }

        return $options;
    }

    /**
     * @param  AttributeRequest  $request
     * @param  Attribute  $attribute
     * @return RedirectResponse
     * @throws Throwable
     */
    public function update(AttributeRequest $request, Attribute $attribute): RedirectResponse
    {
        try {
            $data = $request->all();
            AttributeRepo::getInstance()->update($attribute, $data);

            return redirect(panel_route('attributes.index'))
                ->with('instance', $attribute)
                ->with('success', common_trans('base.updated_success'));
        } catch (Exception $e) {
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

            return back()->with('success', common_trans('base.deleted_success'));
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
