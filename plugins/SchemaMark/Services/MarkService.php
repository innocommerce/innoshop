<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\SchemaMark\Services;

use Exception;
use InnoShop\Common\Services\BaseService;

class MarkService extends BaseService
{
    /**
     * @param  $type
     * @param  $instance
     * @return array
     * @throws Exception
     */
    public function getTagData($type, $instance): array
    {
        if ($type == 'product') {
            return $this->getProductTagData($instance);
        } elseif ($type == 'article') {
            return $this->getArticleTagData($instance);
        }

        return [];
    }

    /**
     * @param  $product
     * @return array
     * @throws Exception
     */
    private function getProductTagData($product): array
    {
        $sku = $product->masterSku;

        return [
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $product->translation->name,
            'image'       => image_resize($sku->image->path ?? ($product->image->path ?? ''), 600, 600),
            'description' => $product->translation->summary,
            'sku'         => $sku->code,
            'mpn'         => '',
            'brand'       => [
                '@type' => 'Brand',
                'name'  => $product->brand->name ?? '',
            ],
            'offers' => [
                '@type'           => 'Offer',
                'url'             => $product->url,
                'priceCurrency'   => current_currency_code(),
                'price'           => round($sku->price),
                'availability'    => 'https://schema.org/InStock',
                'itemCondition'   => 'https://schema.org/NewCondition',
                'priceValidUntil' => now()->endOfYear()->format('Y-m-d'),
            ],
        ];
    }

    /**
     * @param  $article
     * @return array
     * @throws Exception
     */
    private function getArticleTagData($article): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'BlogPosting',
            'headline' => $article->translation->title,
            'author'   => [
                '@type' => 'Person',
                'name'  => $article->author,
            ],
            'datePublished'    => $article->created_at->format('Y-m-d'),
            'dateModified'     => $article->updated_at->format('Y-m-d'),
            'description'      => $article->translation->summary,
            'image'            => image_resize($article->image, 600, 600),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                'url'   => $article->url,
            ],
        ];
    }
}
