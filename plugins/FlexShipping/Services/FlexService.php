<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\FlexShipping\Services;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Models\Checkout;
use InnoShop\Common\Services\CheckoutService;
use Throwable;

final class FlexService
{
    private CheckoutService $checkoutService;

    private Checkout $checkout;

    private $address;

    private $cartOrder;

    private $cartList;

    private $rawQuote;

    private $logger;

    private $customer;

    /**
     * @param  CheckoutService  $checkoutService
     * @throws Throwable
     */
    public function __construct(CheckoutService $checkoutService)
    {
        $checkout = $checkoutService->getCheckout();

        $this->checkout        = $checkout;
        $this->checkoutService = $checkoutService;
        $this->address         = $checkout->shippingAddress;
        $this->cartList        = $checkoutService->getCartList();
        $this->customer        = $checkout->customer;
    }

    /**
     * 获取 FlexService 对象实例
     *
     * @param  CheckoutService  $checkoutService
     * @return FlexService
     * @throws Throwable
     */
    public static function getInstance(CheckoutService $checkoutService): self
    {
        return new self($checkoutService);
    }

    /**
     * @throws Exception
     */
    public function getQuote($rawQuote): ?array
    {
        $this->log(__METHOD__);

        $this->rawQuote = $rawQuote;
        $this->log('Raw quote: '.json_encode($rawQuote));

        $this->log('Validating quote rules...');
        if (! $this->validateQuoteRules()) {
            $this->log('Validation failed, exit');

            return null;
        }

        $cost = $this->calculateQuoteCost();
        if (is_null($cost)) {
            $this->log('Cost null, exit');

            return null;
        }

        // 调整费用：未固定费用外，设置额外费用及最大费用值
        if (Arr::get($this->rawQuote, 'cost.unit') != 'flat') {
            $extra = (float) Arr::get($this->rawQuote, 'cost.extra');
            $this->log("Extra cost: {$extra}");
            $cost += $extra;
            $this->log("Cost + extra = {$extra}");

            $max = (float) Arr::get($this->rawQuote, 'cost.max');
            $this->log("Max: {$max}");

            if ($max > 0) {
                $cost = min($cost, $max);
                $this->log("Max vs cost: {$cost}");
            }

            $this->log("Final cost: {$cost}");
        }

        $localeCode = locale();
        $title      = $this->rawQuote['title'][$localeCode] ?? '';
        $this->log("Title: {$title}");

        $description = $this->rawQuote['description'][$localeCode] ?? '';
        $this->log("Description: {$description}");

        $taxClassId = (int) Arr::get($this->rawQuote, 'tax_class_id');
        $this->log("Tax class ID: {$taxClassId}");

        $text = $cost;
        // $text = $this->currency->format($this->tax->calculate($cost, $taxClassId, oc_config('config_tax')), registry('currency'));
        $this->log("Text: {$text}");

        return [
            'type'        => 'shipping',
            'name'        => $title,
            'description' => $description,
            'icon'        => image_resize($this->rawQuote['icon'] ?? ''),
            'cost'        => $cost,
        ];
    }

    // Validate rules
    private function validateQuoteRules(): bool
    {
        $this->log(__METHOD__);

        // Status
        if (! (bool) Arr::get($this->rawQuote, 'status')) {
            $this->log('Quote disabled, exit.');

            return false;
        }

        // Store
        $this->log('Validating store rule...');
        if (! $this->_validateStoreRule(Arr::get($this->rawQuote, 'rules.store'))) {
            $this->log('Failed to pass store rule validation, exit.');

            return false;
        }
        // Geo zone
        if (! $this->_validateGeoZoneRule(Arr::get($this->rawQuote, 'rules.geo_zone'))) {
            return false;
        }
        // Customer group
        if (! $this->_validateCustomerGroupRule(Arr::get($this->rawQuote, 'rules.customer_group'))) {
            return false;
        }
        // Country
        if (! $this->_validateCountryRule(Arr::get($this->rawQuote, 'rules.country'))) {
            return false;
        }
        // Zone
        if (! $this->_validateZoneRule(Arr::get($this->rawQuote, 'rules.zone'))) {
            return false;
        }
        // Currency
        if (! $this->_validateCurrencyRule(Arr::get($this->rawQuote, 'rules.currency'))) {
            return false;
        }
        // Product
        if (! $this->_validateProductRule(Arr::get($this->rawQuote, 'rules.product'))) {
            return false;
        }
        // Category
        if (! $this->_validateCategoryRule(Arr::get($this->rawQuote, 'rules.category'))) {
            return false;
        }
        // Manufacturer
        if (! $this->_validateManufacturerRule(Arr::get($this->rawQuote, 'rules.manufacturer'))) {
            return false;
        }
        // Weekday
        if (! $this->_validateWeekdayRule(Arr::get($this->rawQuote, 'rules.weekdays'))) {
            return false;
        }
        // Time range
        if (! $this->_validateTimeRangeRule(Arr::get($this->rawQuote, 'rules.time'))) {
            return false;
        }

        return true;
    }

    private function _validateStoreRule($rule)
    {
        $this->log(__METHOD__);

        if (Arr::get($rule, 'type') == 'all') {
            $this->log('Enabled for ALL store, passed.');

            return true;
        }

        $storeIds = Arr::get($rule, 'ids');
        $this->log('Enabled for selected stores: '.json_encode($storeIds));

        if (! $storeIds) {
            $this->log('No store selected, failed.');

            return false;
        }

        $storeId = (int) $this->config('config_store_id');
        $this->log("Current store: {$storeId}");

        if (in_array($storeId, $storeIds)) {
            $this->log('Current store in selected stores, passed.');

            return true;
        }

        $this->log('Current store not in selected stores, failed.');

        return false;
    }

    private function _validateGeoZoneRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        if ($type == 'all') {
            $this->log('Enabled for ALL geo zones, passed.');

            return true;
        }

        $geoZoneIds = Arr::get($rule, 'ids');
        $this->log('Enabled for selected geo zones: '.json_encode($geoZoneIds));

        if (empty($geoZoneIds)) {
            $this->log('No geo zone selected, failed.');

            return false;
        }

        $geoZones = $this->_getZoneToGeoZoneId($geoZoneIds);
        if ($geoZones && $geoZones->count()) {
            $this->log('geo zones found, passed.');

            return true;
        }

        $this->log('Not in geo zone, failed');

        return false;
    }

    private function _validateCountryRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        if ($type == 'all') {
            $this->log('Enabled for ALL countries, passed.');

            return true;
        }

        $countryIds = Arr::get($rule, 'ids');
        $this->log('Enabled for selected countries: '.json_encode($countryIds));

        $countryId = (int) $this->address['country_id'];
        $this->log("Current country: {$countryId}");

        if (in_array($countryId, $countryIds)) {
            $this->log('Current country in selected countries, passed.');

            return true;
        }

        $this->log('Current country not in selected countries, failed.');

        return false;
    }

    private function _validateZoneRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        if ($type == 'all') {
            $this->log('Enabled for ALL zones, passed.');

            return true;
        }

        $zoneIds = Arr::get($rule, 'ids');
        $this->log('Enabled for selected zones: '.json_encode($zoneIds));

        $zoneId = (int) $this->address['zone_id'];
        $this->log("Current zone: {$zoneId}");

        if (in_array($zoneId, $zoneIds)) {
            $this->log('Current zone in selected zones, passed.');

            return true;
        }

        $this->log('Current zone not in selected zones, failed.');

        return false;
    }

    private function _validateCustomerGroupRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        if ($type == 'all') {
            $this->log('Enabled for ALL customer groups, passed.');

            return true;
        }

        $customerGroupIds = Arr::get($rule, 'ids');
        $this->log('Enabled for selected customer groups: '.json_encode($customerGroupIds));

        $customerGroupId = (int) $this->customer->customer_group_id;
        $this->log("Current customer group: {$customerGroupId}");

        if (in_array($customerGroupId, $customerGroupIds)) {
            $this->log('Current customer group in customer groups, passed.');

            return true;
        }

        $this->log('Current customer group not in customer groups, failed.');

        return false;
    }

    private function _validateCurrencyRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        if ($type == 'all') {
            $this->log('Enabled for ALL currencies, passed.');

            return true;
        }

        $currencyIds = Arr::get($rule, 'ids');
        $this->log('Enabled for selected currencies: '.json_encode($currencyIds));

        $currencyId = $this->_getCurrencyId();
        $this->log("Current currency: {$currencyId}");

        if (in_array($currencyId, $currencyIds)) {
            $this->log('Current currency in selected currencies, passed.');

            return true;
        }

        $this->log('Current currency not in selected currencies, failed.');

        return false;
    }

    private function _validateProductRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        $this->log("Rule type: {$type}");

        if ($type == 'all') {
            $this->log('Enabled for ALL products (all), passed.');

            return true;
        }

        $products   = Arr::get($rule, 'items');
        $productIds = array_column($products, 'id');
        $this->log('Selected products: '.json_encode($productIds));

        $cartProductIds = $this->_getCartProductIds();
        $cartProductIds = array_unique($cartProductIds);
        $this->log('Current cart products: '.json_encode($cartProductIds));

        if ($type == 'only') {
            $intersects = array_intersect($productIds, $cartProductIds);
            $this->log('Products in selected list (only): '.json_encode($intersects));
            if (count($intersects) == count($cartProductIds)) {
                $this->log('All cart products in selected products list, passed.');

                return true;
            }

            $this->log('Not all cart products in selected products list, failed.');

            return false;
        }

        if ($type == 'include') {
            $intersects = array_intersect($productIds, $cartProductIds);
            $this->log('Products in selected list (include): '.json_encode($intersects));

            $count = count($intersects);
            if ($count) {
                $this->log("{$count} products in selected list (include), passed.");

                return true;
            }

            $this->log("{$count} products in selected list (include), failed.");

            return false;
        }

        if ($type == 'exclude') {
            $intersects = array_intersect($productIds, $cartProductIds);
            $this->log('Products in selected list (exclude): '.json_encode($intersects));

            $count = count($intersects);
            if (! $count) {
                $this->log("{$count} products in selected list (exclude), passed.");

                return true;
            }

            $this->log("{$count} products in selected list (exclude), failed.");

            return false;
        }

        return false;
    }

    private function _validateCategoryRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        $this->log("Rule type: {$type}");

        if ($type == 'all') {
            $this->log('Enabled for ALL categories (all), passed.');

            return true;
        }

        $categories  = Arr::get($rule, 'items');
        $categoryIds = array_column($categories, 'value');
        $this->log('Selected categories: '.json_encode($categoryIds));

        $cartProductCategories = $this->_getCartProductCategories();

        if ($type == 'only') {
            foreach ($cartProductCategories as $cartProductId => $cartProductCategoryIds) {
                $this->log("Cart product: {$cartProductId} - categories: ".json_encode($cartProductCategoryIds));
                $intersects = array_intersect($categoryIds, $cartProductCategoryIds);
                if (count($intersects) != count($categoryIds)) {
                    $this->log('Not all cart product categories in selected category list, failed.');

                    return false;
                }
            }

            $this->log('All product categories in selected category list, passed.');

            return true;
        }

        if ($type == 'include') {
            foreach ($cartProductCategories as $cartProductId => $cartProductCategoryIds) {
                $this->log("Cart product: {$cartProductId} - categories: ".json_encode($cartProductCategoryIds));
                $intersects = array_intersect($categoryIds, $cartProductCategoryIds);
                if ($intersects) {
                    $this->log('Cart product categories: '.json_encode($intersects).'in selected list (include), passed.');

                    return true;
                }
            }
            $this->log('No product categories in selected category list (include), failed.');

            return false;
        }

        if ($type == 'exclude') {
            foreach ($cartProductCategories as $cartProductId => $cartProductCategoryIds) {
                $this->log("Cart product: {$cartProductId} - categories: ".json_encode($cartProductCategoryIds));
                $intersects = array_intersect($categoryIds, $cartProductCategoryIds);
                if ($intersects) {
                    $this->log('Cart product categories: '.json_encode($intersects).'in selected list (exclude), failed.');

                    return false;
                }
            }

            $this->log('No product categories in selected category list (exclude), passed.');

            return true;
        }

        return false;
    }

    private function _validateManufacturerRule($rule)
    {
        $this->log(__METHOD__);

        $type = Arr::get($rule, 'type', 'all');
        $this->log("Rule type: {$type}");

        if ($type == 'all') {
            $this->log('Enabled for ALL manufacturers (all), passed.');

            return true;
        }

        $manufacturers   = Arr::get($rule, 'items');
        $manufacturerIds = array_column($manufacturers, 'value');
        $this->log('Selected manufacturers: '.json_encode($manufacturerIds));

        $cartProductManufacturerIds = $this->_getCartProductManufacturers();
        $cartProductManufacturerIds = array_unique($cartProductManufacturerIds);
        $this->log('Cart product manufacturers: '.json_encode($cartProductManufacturerIds));

        if ($type == 'only') {
            $intersects = array_intersect($manufacturerIds, $cartProductManufacturerIds);
            $this->log('Cart product manufacturers in selected list (only): '.json_encode($cartProductManufacturerIds));

            if (count($intersects) == count($cartProductManufacturerIds)) {
                $this->log('All cart product manufacturers in selected category list (only), passed.');

                return true;
            }

            $this->log('Not all cart product manufacturers in selected category list (only), failed.');

            return false;
        }

        if ($type == 'include') {
            $intersects = array_intersect($manufacturerIds, $cartProductManufacturerIds);
            $this->log('Cart product manufacturers in selected list (include): '.json_encode($cartProductManufacturerIds));

            $count = count($intersects);
            if ($count) {
                $this->log("{$count} cart product manufacturers in selected list (include), passed.");

                return true;
            }

            $this->log('No cart product manufacturers in selected list (include), failed.');

            return false;
        }

        if ($type == 'exclude') {
            $intersects = array_intersect($manufacturerIds, $cartProductManufacturerIds);
            $this->log('Cart product manufacturers in selected list (exclude): '.json_encode($cartProductManufacturerIds));

            $count = count($intersects);
            if (! $count) {
                $this->log('No cart product manufacturers in selected list (include), passed.');

                return true;
            }

            $this->log("{$count} cart product manufacturers in selected list (include), failed.");

            return false;
        }

        return false;
    }

    private function _validateWeekdayRule($weekdays)
    {
        $this->log(__METHOD__);

        if (! $weekdays) {
            $this->log('No weekdays selected, failed.');

            return false;
        }

        // https://www.php.net/manual/en/function.date.php
        $today = (int) date('N');
        $this->log("Today weekday number: {$today}");

        if (! in_array($today, $weekdays)) {
            $this->log("Today {$today} not in selected weekday list ".json_encode($weekdays).', failed.');

            return false;
        }

        $this->log("Today {$today} in selected weekday list ".json_encode($weekdays).', passed.');

        return true;
    }

    private function _validateTimeRangeRule($rule)
    {
        $this->log(__METHOD__);

        $start = Arr::get($rule, 'start');
        $end   = Arr::get($rule, 'end');
        $this->log("Start: {$start}");
        $this->log("End: {$end}");

        if ($start == 'any' && $end == 'any') {
            $this->log('start = any, end = any, passed.');

            return true;
        }

        $now = date('H:i');
        $this->log("Now: {$now}");

        if ($start == 'any') {
            return $now <= $end;
        }
        if ($end == 'any') {
            return $now >= $start;
        }

        return ($now >= $start) && ($now <= $end);
    }

    // Calculate costs
    private function calculateQuoteCost()
    {
        $this->log(__METHOD__);

        $unitType = Arr::get($this->rawQuote, 'cost.unit');
        $this->log("Unit type: {$unitType}");

        // Handle flat first
        if ($unitType == 'flat') {
            $cost = max((float) Arr::get($this->rawQuote, 'cost.flat_cost'), 0);
            $this->log('Flat cost: {$cost}');

            return $cost;
        }

        // Calculate unit value
        $unit = 0;
        switch ($unitType) {
            case 'weight':
                $unit = $this->_getCartWeight();
                $this->log("Weight: {$unit}");

                break;
            case 'subtotal':
                $unit = $this->_getCartSubtotal();
                $this->log("Subtotal: {$unit}");

                break;
            case 'total_quantity':
                $unit = $this->_getCartTotalQuantity();
                $this->log("Total quantity: {$unit}");

                break;
            case 'volume':
                $unit = $this->_getCartProductTotalVolumes();
                $this->log("Volume: {$unit}");

                break;
            case 'volume_weight':
            case 'volume_weight_max':
                $totalVolumes = $this->_getCartProductTotalVolumes();
                $this->log("Volume: {$unit}");

                $operator = Arr::get($this->rawQuote, 'cost.ratio.operator');
                $constant = (float) Arr::get($this->rawQuote, 'cost.ratio.constant');
                $this->log("operator: {$operator}, constant: {$constant}");

                if ($operator && $constant > 0) {
                    switch ($operator) {
                        case 'add':
                            $unit = $totalVolumes + $constant;

                            break;
                        case 'subtract':
                            $unit = $totalVolumes - $constant;

                            break;
                        case 'multiply':
                            $unit = $totalVolumes * $constant;

                            break;
                        case 'divide':
                            $unit = $totalVolumes / $constant;

                            break;
                    }
                }
                $this->log("Volume weight: {$unit}");

                if ($unitType == 'volume_weight_max') {
                    $totalWeight = $this->_getCartWeight();
                    $unit        = max($unit, $totalWeight);
                }
                $this->log("Final volume weight: {$unit}");

                break;
            default:
                $this->log("Unknown unit type: {$unitType}");

                break;
        }

        $this->log("Final unit: {$unit}");

        $costType = Arr::get($this->rawQuote, 'cost.type'); // range/cumulative
        $this->log("Cost type: {$costType}");

        $ranges = Arr::get($this->rawQuote, 'cost.ranges');
        if (! $ranges) {
            $this->log('No ranges, return null');

            return null;
        }

        if ($costType != 'cumulative') { // Range cost
            foreach ($ranges as $index => $range) {
                $this->log("Looping ranges index: {$index}");

                $start = (float) Arr::get($range, 'start');
                $end   = (float) Arr::get($range, 'end');
                $cost  = (float) Arr::get($range, 'cost');
                $block = (float) Arr::get($range, 'block');
                $this->log("Range: {$start} - {$end}, cost: {$cost}, block: {$block}");

                if ($unit < $start || $unit > $end) { // Out of range
                    $this->log("Unit {$unit} out of range: {$start} - {$end}, skipped.");

                    continue;
                }

                // In range
                $this->log("Unit {$unit} in range: {$start} - {$end}");

                if ($block > 0) {
                    $this->log('Block cost');

                    $unit = ceil($unit / $block);
                    $cost *= $unit;
                    $this->log("Unit: {$unit}, cost: {$cost}");
                }

                $this->log("Final cost: {$cost}, break.");

                return $cost;
            }
        } else { // Cumulative cost
            $cumulatedCost = 0;
            $cumulatedUnit = 0;

            foreach ($ranges as $index => $range) {
                $this->log("Looping ranges index: {$index}");

                $start = (float) Arr::get($range, 'start');
                $end   = (float) Arr::get($range, 'end');
                $cost  = (float) Arr::get($range, 'cost');
                $block = (float) Arr::get($range, 'block');
                $this->log("Range: {$start} - {$end}, cost: {$cost}, block: {$block}");

                // 第 1 条规则，是否满足最小值？
                if ($index == 0) {
                    if ($unit < $start) {
                        $this->log("Unit: {$unit} 小于 start: {$start}，退出");

                        return null;
                    }
                }

                // Out of range
                if ($unit > $end) {
                    $this->log("Unit {$unit} out of range: {$start} - {$end}, skipped.");

                    if ($block > 0) {
                        $this->log('Block cost');

                        $blockedUnit = ceil(($end - $cumulatedUnit) / $block);
                        $this->log("Blocked unit: {$blockedUnit}");

                        $this->log("Blocked cost: {$cost} * {$blockedUnit} = ".($cost * $blockedUnit));
                        $cost *= $blockedUnit;
                    }

                    $this->log("Range cost: {$cost}");

                    $cumulatedCost += $cost;
                    $cumulatedUnit = $end;
                    $this->log("Cumulated cost: {$cumulatedCost}");
                    $this->log("Cumulated unit: {$cumulatedUnit}");

                    continue;
                }

                // In range
                $this->log("Unit {$unit} in range: {$start} - {$end}");

                if ($block > 0) {
                    $this->log('Block > 0, calculate block cost.');

                    $unit -= $cumulatedUnit;
                    $this->log("Remaining unit: {$unit}");

                    $blockUnit = ceil($unit / $block);
                    $this->log("Block unit: {$blockUnit}");

                    $this->log("Block cost: {$cost} * {$blockUnit} = ".($cost * $blockUnit));
                    $cost *= $blockUnit;
                }

                $this->log("Range cost: {$cost}");

                $cumulatedCost += $cost;
                $this->log("Cumulated cost: {$cumulatedCost}");

                $this->log("Final cost: {$cumulatedCost}, break.");

                return $cumulatedCost;
            }
        }

        $this->log('Final cost: null');

        return null;
    }

    // Private helpers
    private function _getCartProductTotalVolumes()
    {
        $volumes               = 0;
        $standardLengthClassId = $this->config('config_length_class_id');
        foreach ($this->_getCartProducts() as $product) {
            $productLengthClassId = $product['length_class_id'] ?? 0;
            if (empty($productLengthClassId)) {
                continue;
            }

            $length = $this->length->convert((float) $product['length'], $productLengthClassId, $standardLengthClassId);
            $width  = $this->length->convert((float) $product['width'], $productLengthClassId, $standardLengthClassId);
            $height = $this->length->convert((float) $product['height'], $productLengthClassId, $standardLengthClassId);
            $volumes += ($length * $width * $height * (int) $product['quantity']);
        }

        return $volumes;
    }

    private function _getCartProducts()
    {
        return $this->cartList;
    }

    private function _getCartProductIds()
    {
        $products   = $this->_getCartProducts();
        $productIds = array_column($products, 'product_id');

        return array_unique($productIds);
    }

    private function _getCartProductCategories()
    {
        $cartProductIds = $this->_getCartProductIds();

        $products = [];
        $items    = ProductCategory::query()->whereIn('product_id', $cartProductIds)->get();
        foreach ($items as $item) {
            $products[$item->product_id][] = (int) $item->category_id;
        }

        return $products;
    }

    private function _getCartProductManufacturers()
    {
        $cartProductIds = $this->_getCartProductIds();

        $products = [];
        $items    = Product::query()->select('product_id', 'brand_id')
            ->whereIn('product_id', $cartProductIds)->get();
        foreach ($items as $item) {
            $products[$item->product_id] = (int) $item->brand_id;
        }

        return $products;
    }

    private function _getCartWeight()
    {
        return $this->checkout->getCartWeight();
    }

    private function _getZoneToGeoZoneId($geoZoneIds)
    {
        if (! $this->address) {
            return [];
        }

        return RegionZone::query()
            ->whereIn('region_id', $geoZoneIds)
            ->where('country_id', $this->address['country_id'])
            ->where(function (Builder $query) {
                $query->where('zone_id', 0)
                    ->orWhere('zone_id', $this->address['zone_id']);
            })
            ->get();
    }

    private function _getCartSubtotal()
    {
        return $this->totalService->getSubTotal();
    }

    private function _getCartTotalQuantity()
    {
        // return $this->totalService->countProducts();

        return collect($this->cartList)->sum('quantity');
    }

    private function _getCurrencyId()
    {
        return current_currency_id();
    }

    private function config($key)
    {
        return plugin_setting($key);
    }

    private function log($message)
    {
        $debug = $this->config('shipping_flex_debug');
        if ($debug) {
            Log::info($message);
        }
    }
}
