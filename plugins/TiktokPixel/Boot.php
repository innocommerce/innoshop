<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\TiktokPixel;

use Exception;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\TiktokPixel\Services\TiktokPixelService;

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
     * See https://ads.tiktok.com/help/article/standard-events-parameters?lang=zh
     * Search, ViewContent, AddToCart, AddToWishlist, InitiateCheckout, PlaceAnOrder, CompletePayment
     *
     * @return void
     * @throws Exception
     */
    private function addSchemaMark(): void
    {
        $pixelID = plugin_setting('tiktok_pixel', 'tiktok_pixel_id');
        if (empty($pixelID)) {
            return;
        }

        listen_blade_insert('front.layout.app.head.bottom', function ($data) {
            return TiktokPixelService::getInstance()->renderTags($data);
        });
    }
}
