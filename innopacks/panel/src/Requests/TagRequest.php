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

class TagRequest extends FormRequest
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
        if ($this->tag) {
            $slugRule = 'required|alpha_dash|unique:tags,slug,'.$this->tag->id;
        } else {
            $slugRule = 'required|alpha_dash|unique:tags,slug';
        }

        $defaultLocale = setting_locale_code();

        $rules = [
            'slug'     => $slugRule,
            'position' => 'integer',
            'active'   => 'bool',

            "translations.$defaultLocale.locale" => 'required',
            "translations.$defaultLocale.name"   => 'required',
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
            "translations.$defaultLocale.locale" => trans('panel/tag.locale'),
            "translations.$defaultLocale.name"   => trans('panel/tag.name'),
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
                'description' => 'Unique tag slug (alpha_dash).',
                'example'     => 'featured',
            ],
            'position' => [
                'description' => 'Sort order.',
                'example'     => 0,
            ],
            'active' => [
                'description' => 'Whether the tag is active.',
                'example'     => true,
            ],
            "translations.$locale.locale" => [
                'description' => 'Locale code for this translation.',
                'example'     => $locale,
            ],
            "translations.$locale.name" => [
                'description' => 'Tag display name.',
                'example'     => 'Featured',
            ],
        ];
    }
}
