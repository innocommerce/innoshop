<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FixedShipping;

use InnoShop\Common\Entities\ShippingEntity;
use InnoShop\Plugin\Core\BaseBoot;

class Boot extends BaseBoot
{
    public function init() {}

    /**
     * Get quotes.
     *
     * @param  ShippingEntity  $entity
     * @return array
     */
    public function getQuotes(ShippingEntity $entity): array
    {
        $code     = $this->plugin->getCode();
        $resource = $this->pluginResource->jsonSerialize();
        $cost     = $this->getShippingFee($entity);
        $quotes[] = [
            'type'        => 'shipping',
            'code'        => "{$code}.0",
            'name'        => $resource['name'],
            'description' => $resource['description'],
            'icon'        => $resource['icon'],
            'cost'        => $cost,
            'cost_format' => currency_format($cost),
        ];

        return $quotes;
    }

    /**
     * Calculate shipping fee.
     *
     * @param  ShippingEntity  $entity
     * @return float|int
     */
    public function getShippingFee(ShippingEntity $entity): float|int
    {
        $subtotal      = $entity->getSubtotal();
        $shippingType  = plugin_setting('fixed_shipping', 'type', 'fixed');
        $shippingValue = plugin_setting('fixed_shipping', 'value', 0);
        if ($shippingType == 'fixed') {
            return $shippingValue;
        } elseif ($shippingType == 'percent') {
            return $subtotal * $shippingValue / 100;
        }

        return 0;
    }
}
