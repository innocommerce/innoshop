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
     * Get location information by IP address
     *
     * @param  string  $ip
     * @return array
     */
    public function getLocation(string $ip): array
    {
        $databasePath = $this->geoLite2Service->getDatabasePath();

        if (empty($databasePath) || ! File::exists($databasePath)) {
            return $this->getDefaultLocation();
        }

        try {
            $databasePath = $this->geoLite2Service->getDatabasePath();

            if ($this->reader === null) {
                $this->reader = new Reader($databasePath);
            }

            $record = $this->reader->city($ip);

            return [
                'country_code' => $record->country->isoCode ?? '',
                'country_name' => $record->country->name ?? '',
                'city'         => $record->city->name ?? '',
                'latitude'     => $record->location->latitude ?? null,
                'longitude'    => $record->location->longitude ?? null,
            ];
        } catch (AddressNotFoundException $e) {
            // IP address not found in database
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
