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

class PageRequest extends FormRequest
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
        if ($this->page) {
            $slugRule = 'required|alpha_dash|unique:pages,slug,'.$this->page->id;
        } else {
            $slugRule = 'required|alpha_dash|unique:pages,slug';
        }

        $defaultLocale = setting_locale_code();

        $rules = [
            'slug'     => $slugRule,
            'key_name' => 'nullable|string',
            'position' => 'integer',
            'active'   => 'bool',

            "translations.$defaultLocale.locale"  => 'required',
            "translations.$defaultLocale.title"   => 'required',
            "translations.$defaultLocale.content" => 'required|string|max:1000000',

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
            "translations.$defaultLocale.locale"  => trans('panel/page.locale'),
            "translations.$defaultLocale.title"   => trans('panel/page.title'),
            "translations.$defaultLocale.content" => trans('panel/page.content'),

            'translations.*.meta_title'       => trans('panel/common.meta_title'),
            'translations.*.meta_description' => trans('panel/common.meta_description'),
            'translations.*.meta_keywords'    => trans('panel/common.meta_keywords'),
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
                'description' => 'Unique page slug (alpha_dash).',
                'example'     => 'about-us',
            ],
            'key_name' => [
                'description' => 'Optional internal key for theme or code references.',
                'example'     => 'about',
            ],
            'position' => [
                'description' => 'Sort order.',
                'example'     => 0,
            ],
            'active' => [
                'description' => 'Whether the page is published.',
                'example'     => true,
            ],
            "translations.$locale.locale" => [
                'description' => 'Locale code for this translation.',
                'example'     => $locale,
            ],
            "translations.$locale.title" => [
                'description' => 'Page title.',
                'example'     => 'About us',
            ],
            "translations.$locale.content" => [
                'description' => 'Page body (HTML allowed, large max length).',
                'example'     => '<p>About our store</p>',
            ],
            'translations.*.meta_title' => [
                'description' => 'SEO meta title (max 500).',
                'example'     => 'About us',
            ],
            'translations.*.meta_keywords' => [
                'description' => 'SEO meta keywords (max 500).',
                'example'     => 'about, company',
            ],
            'translations.*.meta_description' => [
                'description' => 'SEO meta description (max 1000).',
                'example'     => 'Learn more about us.',
            ],
        ];
    }
}
