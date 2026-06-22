<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use GeoIp2\Database\Reader;
use GeoIp2\Exception\AddressNotFoundException;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;

class GeoLocationService
{
    /**
     * GeoLite2 database reader instance
     *
     * @var Reader|null
     */
    private ?Reader $reader = null;

    /**
     * GeoLite2 service instance
     *
     * @var GeoLite2Service
     */
    private GeoLite2Service $geoLite2Service;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->geoLite2Service = new GeoLite2Service;
    }

    /**
     * Get location information by IP address.
     * Tries local GeoIP database first, then falls back to plugin-provided
     * remote lookups via the geo_location.lookup hook.
     *
     * @param  string  $ip
     * @return array
     */
    public function getLocation(string $ip): array
    {
        $result = $this->lookupLocalDatabase($ip);

        if (empty($result['country_name']) && empty($result['city'])) {
            $result['ip'] = $ip;
            $result       = fire_hook_filter('geo_location.lookup', $result);
            unset($result['ip']);
        }

        return $result;
    }

    /**
     * Lookup location from local GeoIP database.
     *
     * @param  string  $ip
     * @return array
     */
    private function lookupLocalDatabase(string $ip): array
    {
        $databasePath = $this->geoLite2Service->getDatabasePath();

        if (empty($databasePath) || ! File::exists($databasePath)) {
            return $this->getDefaultLocation();
        }

        try {
            if ($this->reader === null) {
                $appLocale = app()->getLocale();
                $localeMap = [
                    'zh'    => 'zh-CN',
                    'zh-cn' => 'zh-CN',
                    'zh-tw' => 'zh-TW',
                ];
                $preferred    = $localeMap[strtolower($appLocale)] ?? $appLocale;
                $this->reader = new Reader($databasePath, [$preferred, 'en']);
            }

            $record      = $this->reader->city($ip);
            $subdivision = $record->subdivisions[0] ?? null;

            return [
                'country_code' => $record->country->isoCode ?? '',
                'country_name' => $record->country->name ?? '',
                'region_code'  => $subdivision?->isoCode ?? '',
                'region_name'  => $subdivision?->name ?? '',
                'city'         => $record->city->name ?? '',
                'latitude'     => $record->location->latitude ?? null,
                'longitude'    => $record->location->longitude ?? null,
            ];
        } catch (AddressNotFoundException $e) {
            return $this->getDefaultLocation();
        } catch (Exception $e) {
            Log::warning('GeoLocationService: Failed to get location', [
                'ip'    => $ip,
                'error' => $e->getMessage(),
            ]);

            return $this->getDefaultLocation();
        }
    }

    /**
     * Get default location (empty)
     *
     * @return array
     */
    private function getDefaultLocation(): array
    {
        return [
            'country_code' => '',
            'country_name' => '',
            'region_code'  => '',
            'region_name'  => '',
            'city'         => '',
            'latitude'     => null,
            'longitude'    => null,
        ];
    }

    /**
     * Check if GeoLite2 database is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->geoLite2Service->isAvailable();
    }
}
