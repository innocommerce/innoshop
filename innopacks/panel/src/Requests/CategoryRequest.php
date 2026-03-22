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
use InnoShop\Common\Traits\PatchRequestTrait;

class CategoryRequest extends FormRequest
{
    use PatchRequestTrait;

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
        if ($this->category) {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:categories,slug,'.$this->category->id;
        } else {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:categories,slug';
        }

        $defaultLocale = setting_locale_code();

        $rules = [
            'slug'     => $slugRule,
            'position' => 'integer',

            "translations.$defaultLocale.locale" => 'required',
            "translations.$defaultLocale.name"   => 'required',

            'translations.*.meta_title'       => 'max:500',
            'translations.*.meta_keywords'    => 'max:500',
            'translations.*.meta_description' => 'max:1000',
        ];

        // For PATCH requests, make all rules optional (sometimes)
        if ($this->isMethod('PATCH')) {
            $rules = $this->applySometimesToRules($rules);
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
            'slug'                               => panel_trans('common.slug'),
            "translations.$defaultLocale.locale" => trans('panel/category.locale'),
            "translations.$defaultLocale.name"   => trans('panel/category.name'),

            'translations.*.meta_title'       => trans('panel/common.meta_title'),
            'translations.*.meta_keywords'    => trans('panel/common.meta_keywords'),
            'translations.*.meta_description' => trans('panel/common.meta_description'),
        ];
    }

    /**
     * @return array<string, array{description?: string, example?: mixed}>
     */
    public function bodyParameters(): array
    {
        $locale = setting_locale_code();

        return [
            'slug' => [
                'description' => 'URL slug (letters, numbers, hyphens). Must be unique per category.',
                'example'     => 'electronics',
            ],
            'position' => [
                'description' => 'Sort order (integer).',
                'example'     => 0,
            ],
            "translations.$locale.locale" => [
                'description' => 'Locale code for this translation row (must match default shop locale for required fields).',
                'example'     => $locale,
            ],
            "translations.$locale.name" => [
                'description' => 'Category name in this locale.',
                'example'     => 'Electronics',
            ],
            'translations.*.meta_title' => [
                'description' => 'SEO meta title (max 500).',
                'example'     => 'Electronics category',
            ],
            'translations.*.meta_keywords' => [
                'description' => 'SEO meta keywords (max 500).',
                'example'     => 'electronics, gadgets',
            ],
            'translations.*.meta_description' => [
                'description' => 'SEO meta description (max 1000).',
                'example'     => 'Browse our electronics collection.',
            ],
        ];
    }
}
