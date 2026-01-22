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
     * Prepare the data for validation.
     * Convert JSON strings to arrays for skus and variants.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        // Convert skus JSON string to array if needed
        // Single SKU products use skus[0][price] format which Laravel handles automatically as array
        // Multiple SKU products use hidden field with JSON string that needs to be decoded
        if ($this->has('skus') && is_string($this->skus)) {
            $decoded = json_decode($this->skus, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['skus' => $decoded]);
            } else {
                // If JSON decode fails, set to empty array to avoid validation error
                $this->merge(['skus' => []]);
            }
        }

        // Convert variants JSON string to array if needed
        if ($this->has('variants') && is_string($this->variants)) {
            $decoded = json_decode($this->variants, true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $this->merge(['variants' => $decoded]);
            }
        }
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
            'type'       => 'required|in:normal,bundle,virtual,card',
            'weight'     => 'nullable|numeric|min:0',

            "translations.$defaultLocale.locale"  => 'required',
            "translations.$defaultLocale.name"    => 'required',
            "translations.$defaultLocale.content" => 'max:20000',

            'translations.*.meta_title'       => 'max:500',
            'translations.*.meta_keywords'    => 'max:500',
            'translations.*.meta_description' => 'max:1000',

            'skus'         => 'array',
            'skus.*.code'  => 'nullable|string|max:32',
            'skus.*.model' => 'nullable|string|max:32',
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
            'slug'   => panel_trans('common.slug'),
            'type'   => panel_trans('product.type'),
            'weight' => panel_trans('product.weight'),

            "translations.$defaultLocale.locale"  => trans('panel/product.locale'),
            "translations.$defaultLocale.name"    => trans('panel/product.name'),
            "translations.$defaultLocale.content" => trans('panel/product.content'),

            "translations.$defaultLocale.meta_title"       => trans('panel/common.meta_title'),
            "translations.$defaultLocale.meta_description" => trans('panel/common.meta_description'),
            "translations.$defaultLocale.meta_keywords"    => trans('panel/common.meta_keywords'),

            'skus.*.code'  => panel_trans('product.sku_code'),
            'skus.*.model' => panel_trans('product.model'),

            'bundles'            => panel_trans('product.bundles'),
            'bundles.*.sku_id'   => panel_trans('product.bundle_sku'),
            'bundles.*.quantity' => panel_trans('product.bundle_quantity'),
        ];
    }
}
