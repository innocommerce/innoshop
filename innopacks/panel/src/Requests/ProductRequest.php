<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        if ($this->product) {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:products,slug,'.$this->product->id;
        } else {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:products,slug';
        }

        $defaultLocale = setting_locale_code();

        $rules = [
            'catalog_id' => 'integer',
            'slug'       => $slugRule,
            'active'     => 'bool',
            'type'       => 'required|in:normal,bundle,virtual,card',

            "translations.$defaultLocale.locale"  => 'required',
            "translations.$defaultLocale.name"    => 'required',
            "translations.$defaultLocale.content" => 'max:20000',

            'translations.*.meta_title'       => 'max:500',
            'translations.*.meta_keywords'    => 'max:500',
            'translations.*.meta_description' => 'max:1000',
        ];

        if ($this->type === 'bundle') {
            $rules['bundles']            = 'required|array';
            $rules['bundles.*.sku_id']   = 'required|exists:product_skus,id';
            $rules['bundles.*.quantity'] = 'required|integer|min:1';
        }

        return $rules;
    }

    /**
     * @return array
     */
    public function attributes(): array
    {
        $defaultLocale = setting_locale_code();

        return [
            'slug' => panel_trans('common.slug'),
            'type' => panel_trans('product.type'),

            "translations.$defaultLocale.locale"  => trans('panel/product.locale'),
            "translations.$defaultLocale.name"    => trans('panel/product.name'),
            "translations.$defaultLocale.content" => trans('panel/product.content'),

            "translations.$defaultLocale.meta_title"       => trans('panel/common.meta_title'),
            "translations.$defaultLocale.meta_description" => trans('panel/common.meta_description'),
            "translations.$defaultLocale.meta_keywords"    => trans('panel/common.meta_keywords'),

            'bundles'            => panel_trans('product.bundles'),
            'bundles.*.sku_id'   => panel_trans('product.bundle_sku'),
            'bundles.*.quantity' => panel_trans('product.bundle_quantity'),
        ];
    }
}
