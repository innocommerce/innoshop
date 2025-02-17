<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OpenGraph\Services;

use Exception;
use InnoShop\Common\Services\BaseService;

class OpenGraphService extends BaseService
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
            'title'       => $product->translation->name,
            'description' => sub_string($product->translation->content, 256),
            'image'       => image_resize($sku->image->path ?? ($product->image->path ?? ''), 600, 600),
            'url'         => $product->url,
            'type'        => 'product',
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
            'title'       => $article->translation->title,
            'description' => sub_string($article->translation->content, 256),
            'image'       => image_resize($article->image, 600, 600),
            'url'         => $article->url,
            'type'        => 'article',
        ];
    }
}
