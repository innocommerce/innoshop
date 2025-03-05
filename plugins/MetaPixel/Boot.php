<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\MetaPixel;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\MetaPixel\Services\MetaPixelService;

class Boot extends BaseBoot
{
    /**
     * @return void
     * @throws Exception
     */
    public function init(): void
    {
        $this->addSchemaMark();
    }

    /**
     * See https://developers.facebook.com/docs/meta-pixel/reference#standard-events
     * Search, ViewContent, AddToCart, AddToWishlist, InitiateCheckout, Purchase
     *
     * @return void
     * @throws Exception
     */
    private function addSchemaMark(): void
    {
        $pixelID = plugin_setting('meta_pixel', 'meta_pixel_id');
        if (empty($pixelID)) {
            return;
        }

        listen_blade_insert('front.layout.app.head.bottom', function ($data) {
            return MetaPixelService::getInstance()->renderTags($data);
        });
    }
}
