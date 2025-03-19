<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlexShipping;

use Exception;
use InnoShop\Common\Entities\ShippingEntity;
use InnoShop\Plugin\Core\BaseBoot;
use Plugin\FlexShipping\Services\FlexService;
use Throwable;

class Boot extends BaseBoot
{
    public function init(): void {}

    /**
     * Get quotes.
     *
     * @param  ShippingEntity  $entity
     * @return array
     * @throws Throwable
     */
    public function getQuotes(ShippingEntity $entity): array
    {
        try {
            //$quoteData = json_decode(file_get_contents(plugin_path('FlexShipping/Storage/demo.json')), true);
            $quoteData = plugin_setting('flex_shipping', 'setting');

            if (! plugin_setting('flex_shipping', 'active')) {
                return [];
            }

            $shippingQuotes = [];
            $flexShipping   = FlexService::getInstance($entity);
            foreach ($quoteData['quotes'] as $index => $quoteSetting) {
                $quote = $flexShipping->getQuote($quoteSetting);
                if (empty($quote)) {
                    continue;
                }
                $quote['code']    = 'flex_shipping.'.$index;
                $shippingQuotes[] = $quote;
            }

            return $shippingQuotes;
        } catch (Exception $e) {
            return [];
        }
    }
}
