<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\ViewTracker\Libraries;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use GeoIp2\Model\City;
use MaxMind\Db\Reader\InvalidDatabaseException;

class GeoIP
{
    private City $record;

    /**
     * @param  string  $ip
     * @throws AddressNotFoundException
     * @throws InvalidDatabaseException
     */
    public function __construct(string $ip)
    {
        $reader = new Reader(plugin_path('ViewTracker/Storage/GeoLite2-City.mmdb'), ['zh-CN', 'en']);

        $this->record = $reader->city($ip);
    }

    /**
     * @param  string  $ip
     * @return GeoIP
     * @throws Exception
     */
    public static function getInstance(string $ip): GeoIP
    {
        return new self($ip);
    }

    /**
     * @return string
     */
    public function getCountry(): string
    {
        return $this->record->country->name ?? '';
    }

    /**
     * @return string
     */
    public function getCity(): string
    {
        return $this->record->city->name ?? '';
    }

    /**
     * @return string
     */
    public function getLocation(): string
    {
        return $this->getCountry().' '.$this->getCity();
    }
}
