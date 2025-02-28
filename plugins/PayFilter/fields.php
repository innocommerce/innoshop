<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

use InnoShop\Common\Repositories\CountryRepo;
use InnoShop\Plugin\Repositories\PluginRepo;

$mappers = $options = [];

$currencies = currencies();

foreach (CountryRepo::getInstance()->getCountries() as $country) {
    $options[] = [
        'value' => $country->id,
        'label' => $country->name,
    ];
}
$billingMethods = PluginRepo::getInstance()->allPlugins()->where('type', 'billing');
foreach ($billingMethods as $method) {
    $mappers[] = [
        'name'        => $method->code,
        'label'       => \Illuminate\Support\Str::studly($method->code),
        'type'        => 'select',
        'options'     => $options,
        'required'    => true,
        'rules'       => 'required',
        'emptyOption' => false,
    ];
}

return $mappers;
