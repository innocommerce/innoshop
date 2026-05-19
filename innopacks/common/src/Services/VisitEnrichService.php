<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use InnoShop\Common\Models\Visit\Visit;

class VisitEnrichService
{
    /**
     * Enrich a visit record's geo data.
     *
     * @param  Visit  $visit
     * @return array ['success' => bool, 'country_name' => string, 'city' => string]
     */
    public function locate(Visit $visit): array
    {
        $service = new GeoLocationService;
        $result  = $service->getLocation($visit->ip_address);

        $visit->update([
            'country_code' => $result['country_code'] ?? '',
            'country_name' => $result['country_name'] ?? '',
            'city'         => $result['city'] ?? '',
        ]);

        return [
            'success'      => true,
            'country_name' => $result['country_name'] ?? '',
            'city'         => $result['city'] ?? '',
        ];
    }

    /**
     * Enrich a visit record's browser/os from user_agent.
     *
     * @param  Visit  $visit
     * @return array ['success' => bool, 'browser' => string, 'os' => string]
     */
    public function parseUA(Visit $visit): array
    {
        $browser = self::detectBrowser($visit->user_agent);
        $os      = self::detectOS($visit->user_agent);

        $visit->update([
            'browser' => $browser,
            'os'      => $os,
        ]);

        return [
            'success' => true,
            'browser' => $browser,
            'os'      => $os,
        ];
    }

    /**
     * Batch enrich all visits with missing data (geo + UA).
     *
     * @return array ['success' => bool, 'updated' => int]
     */
    public function batchLocate(): array
    {
        $geoService = new GeoLocationService;

        $visits = Visit::where(function ($q) {
            $q->whereNull('country_name')
                ->orWhere('country_name', '')
                ->orWhereNull('city')
                ->orWhere('city', '')
                ->orWhereNull('browser')
                ->orWhere('browser', '')
                ->orWhereNull('os')
                ->orWhere('os', '');
        })
            ->limit(500)
            ->get();

        $updated = 0;

        foreach ($visits as $visit) {
            $fields = [];

            if ($visit->ip_address && (empty($visit->country_name) || empty($visit->city))) {
                $result = $geoService->getLocation($visit->ip_address);
                if (! empty($result['country_name']) || ! empty($result['city'])) {
                    $fields['country_code'] = $result['country_code'] ?? '';
                    $fields['country_name'] = $result['country_name'] ?? '';
                    $fields['city']         = $result['city'] ?? '';
                }
            }

            if ($visit->user_agent && (empty($visit->browser) || empty($visit->os))) {
                $fields['browser'] = self::detectBrowser($visit->user_agent);
                $fields['os']      = self::detectOS($visit->user_agent);
            }

            if (! empty($fields)) {
                $visit->update($fields);
                $updated++;
            }
        }

        return [
            'success' => true,
            'updated' => $updated,
        ];
    }

    public static function detectBrowser(string $ua): string
    {
        $patterns = [
            'Edg/'            => 'Edge',
            'OPR/'            => 'Opera',
            'Opera'           => 'Opera',
            'Vivaldi/'        => 'Vivaldi',
            'Brave/'          => 'Brave',
            'SamsungBrowser/' => 'Samsung Browser',
            'UCBrowser/'      => 'UC Browser',
            'MicroMessenger/' => 'WeChat',
            'QQBrowser/'      => 'QQ Browser',
            'Firefox/'        => 'Firefox',
            'FxiOS/'          => 'Firefox',
            'Chrome/'         => 'Chrome',
            'CriOS/'          => 'Chrome',
            'Safari/'         => 'Safari',
            'MSIE'            => 'IE',
            'Trident/'        => 'IE',
        ];

        foreach ($patterns as $pattern => $name) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return '';
    }

    public static function detectOS(string $ua): string
    {
        $patterns = [
            'HarmonyOS'     => 'HarmonyOS',
            'Android'       => 'Android',
            'iPhone'        => 'iOS',
            'iPad'          => 'iPadOS',
            'iPod'          => 'iOS',
            'Windows Phone' => 'Windows Phone',
            'Windows NT'    => 'Windows',
            'Mac OS X'      => 'macOS',
            'Macintosh'     => 'macOS',
            'Linux'         => 'Linux',
            'CrOS'          => 'Chrome OS',
        ];

        foreach ($patterns as $pattern => $name) {
            if (str_contains($ua, $pattern)) {
                return $name;
            }
        }

        return '';
    }
}
