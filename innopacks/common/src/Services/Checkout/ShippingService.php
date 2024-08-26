<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\Checkout;

use Exception;
use Illuminate\Support\Str;
use InnoShop\Plugin\Core\Plugin;
use InnoShop\Plugin\Repositories\PluginRepo;

class ShippingService extends BaseService
{
    public static ?array $shippingMethods = null;

    /**
     * @throws Exception
     */
    public function getMethods(): array
    {
        if (! is_null(self::$shippingMethods)) {
            return self::$shippingMethods;
        }

        $shippingPlugins = PluginRepo::getInstance()->getShippingMethods();

        $shippingMethods = [];
        foreach ($shippingPlugins as $shippingPlugin) {
            $plugin = $shippingPlugin->plugin;

            $bootClass = $this->getBootClass($plugin);
            if (! method_exists($bootClass, 'getQuotes')) {
                throw new Exception(front_trans('checkout.shipping_quote_error', ['classname' => $bootClass]));
            }

            $quotes = (new $bootClass)->getQuotes($this->checkoutService);

            if ($quotes) {
                $shippingMethods[] = [
                    'code'   => $plugin->getCode(),
                    'name'   => $plugin->getLocaleName(),
                    'quotes' => $quotes,
                ];
            }
        }

        fire_hook_filter('common.service.checkout.shipping.methods', $shippingMethods);

        return self::$shippingMethods = $shippingMethods;
    }

    /**
     * @param  Plugin  $shippingPlugin
     * @return string
     */
    private function getBootClass(Plugin $shippingPlugin): string
    {
        $pluginCode = $shippingPlugin->getCode();
        $pluginName = Str::studly($pluginCode);

        return "Plugin\\{$pluginName}\\Boot";
    }
}
