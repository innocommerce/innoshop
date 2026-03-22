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

class ArticleRequest extends FormRequest
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
        if ($this->article) {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:articles,slug,'.$this->article->id;
        } else {
            $slugRule = 'nullable|regex:/^[a-zA-Z0-9-]+$/|unique:articles,slug';
        }

        $defaultLocale = setting_locale_code();

        $rules = [
            'catalog_id' => 'nullable|integer',
            'slug'       => $slugRule,
            'position'   => 'integer',
            'viewed'     => 'integer',
            'image'      => 'nullable|string|max:500',

            "translations.$defaultLocale.locale"  => 'required',
            "translations.$defaultLocale.title"   => 'required',
            "translations.$defaultLocale.content" => 'required|max:20000',

            'translations.*.summary'          => 'max:320',
            'translations.*.meta_title'       => 'max:500',
            'translations.*.meta_keywords'    => 'max:500',
            'translations.*.meta_description' => 'max:1000',

            // Related articles validation
            'related_articles'              => 'nullable|array',
            'related_articles.*.related_id' => 'required|integer|exists:articles,id',

            // Related products validation
            'article_products'              => 'nullable|array',
            'article_products.*.product_id' => 'required|integer|exists:products,id',
        ];

        // For PATCH requests, make all rules optional (sometimes)
        if ($this->isMethod('PATCH')) {
            $rules = $this->applySometimesToRules($rules);
        }

        return $rules;
    }

    public function attributes(): array
    {
        $defaultLocale = setting_locale_code();

        return [
            'catalog_id' => trans('panel/article.catalog'),

            "translations.$defaultLocale.locale"  => trans('panel/article.locale'),
            "translations.$defaultLocale.title"   => trans('panel/article.title'),
            "translations.$defaultLocale.content" => trans('panel/article.content'),

            'translations.*.summary'          => trans('panel/article.summary'),
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
            'catalog_id' => [
                'description' => 'Optional article catalog/category ID.',
                'example'     => 1,
            ],
            'slug' => [
                'description' => 'URL slug (letters, numbers, hyphens). Unique per article.',
                'example'     => 'summer-sale-guide',
            ],
            'position' => [
                'description' => 'Sort order.',
                'example'     => 0,
            ],
            'viewed' => [
                'description' => 'View counter (integer).',
                'example'     => 0,
            ],
            'image' => [
                'description' => 'Featured image path or URL (max 500).',
                'example'     => 'static/uploads/article/cover.jpg',
            ],
            "translations.$locale.locale" => [
                'description' => 'Locale code for this translation.',
                'example'     => $locale,
            ],
            "translations.$locale.title" => [
                'description' => 'Article title.',
                'example'     => 'Summer sale announcement',
            ],
            "translations.$locale.content" => [
                'description' => 'Article body HTML or text (max 20000).',
                'example'     => '<p>Content here</p>',
            ],
            'translations.*.summary' => [
                'description' => 'Short summary (max 320).',
                'example'     => 'Brief teaser for listings.',
            ],
            'translations.*.meta_title' => [
                'description' => 'SEO meta title (max 500).',
                'example'     => 'Summer sale',
            ],
            'translations.*.meta_keywords' => [
                'description' => 'SEO meta keywords (max 500).',
                'example'     => 'sale, summer',
            ],
            'translations.*.meta_description' => [
                'description' => 'SEO meta description (max 1000).',
                'example'     => 'Read our summer sale article.',
            ],
            'related_articles' => [
                'description' => 'Optional list of related articles.',
                'example'     => [['related_id' => 2]],
            ],
            'related_articles.*.related_id' => [
                'description' => 'Related article ID (must exist).',
                'example'     => 2,
            ],
            'article_products' => [
                'description' => 'Optional linked products.',
                'example'     => [['product_id' => 10]],
            ],
            'article_products.*.product_id' => [
                'description' => 'Product ID (must exist).',
                'example'     => 10,
            ],
        ];
    }
}
