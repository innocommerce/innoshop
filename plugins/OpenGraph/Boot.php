<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OpenGraph;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\OpenGraph\Services\OpenGraphService;

class Boot extends BaseBoot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->addOpenGraphTags();
    }

    /**
     * @return void
     * @throws Exception
     */
    private function addOpenGraphTags(): void
    {
        listen_blade_insert('front.layout.app.head.bottom', function ($data) {
            $routeName = request()->route()->getName();
            $routeName = str_replace(locale_code().'.front.', '', $routeName);

            if (in_array($routeName, ['products.show', 'products.slug_show'])) {
                $type     = 'product';
                $instance = $data['product'];
            } elseif (in_array($routeName, ['articles.show', 'articles.slug_show'])) {
                $type     = 'article';
                $instance = $data['article'];
            }

            if (empty($type) || empty($instance)) {
                return '';
            }

            $tagData = OpenGraphService::getInstance()->getTagData($type, $instance);
            if (empty($tagData)) {
                return '';
            }

            return view('OpenGraph::og_tags', $tagData);
        });
    }
}
