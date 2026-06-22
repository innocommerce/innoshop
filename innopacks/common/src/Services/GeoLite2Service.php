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
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeoLite2Service
{
    /**
     * Default download URL
     *
     * @var string
     */
    private string $defaultDownloadUrl = 'https://res.innoshop.net/GeoLite2-City.mmdb';

    /**
     * Constructor — ensure the database directory exists.
     */
    public function __construct()
    {
        $dir = dirname($this->getDatabasePath());
        if (! File::exists($dir)) {
            File::makeDirectory($dir, 0755, true);
        }
    }

    /**
     * Download GeoLite2 database
     *
     * @param  string|null  $url
     * @return array
     */
    public function downloadDatabase(?string $url = null): array
    {
        try {
            $url = $url ?: $this->defaultDownloadUrl;

            if (empty($url)) {
                return [
                    'success' => false,
                    'message' => __('panel/setting_geolite2.download_url_empty'),
                ];
            }

            // Download file
            $response = Http::timeout(300)->get($url);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => __('panel/setting_geolite2.download_failed', [
                        'error' => 'HTTP '.$response->status(),
                    ]),
                ];
            }

            $content = $response->body();

            if (empty($content)) {
                return [
                    'success' => false,
                    'message' => __('panel/setting_geolite2.download_failed', [
                        'error' => __('panel/setting_geolite2.download_empty'),
                    ]),
                ];
            }

            // Save file
            $path = $this->getDatabasePath();
            File::put($path, $content);

            // Verify database file
            try {
                $reader = new Reader($path);
                $reader->city('8.8.8.8'); // Test query
                $reader = null;
            } catch (Exception $e) {
                File::delete($path);

                return [
                    'success' => false,
                    'message' => __('panel/setting_geolite2.download_failed', [
                        'error' => __('panel/setting_geolite2.verify_failed', ['error' => $e->getMessage()]),
                    ]),
                ];
            }

            return [
                'success' => true,
                'message' => __('panel/setting_geolite2.download_success'),
                'path'    => $path,
            ];
        } catch (Exception $e) {
            Log::error('GeoLite2Service: Failed to download database', [
                'url'   => $url,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => __('panel/setting_geolite2.download_failed', [
                    'error' => $e->getMessage(),
                ]),
            ];
        }
    }

    /**
     * Get database information
     *
     * @return array
     */
    public function getDatabaseInfo(): array
    {
        $path = $this->getDatabasePath();

        // 清除文件系统缓存，确保获取最新状态
        clearstatcache(true, $path);

        $exists   = File::exists($path);
        $size     = $exists ? File::size($path) : 0;
        $modified = $exists ? File::lastModified($path) : 0;

        $version = '';

        if ($exists) {
            try {
                $reader   = new Reader($path);
                $metadata = $reader->metadata();
                $version  = $metadata->databaseType ?? '';
                $reader   = null;
            } catch (Exception $e) {
                Log::warning('GeoLite2Service: Failed to read metadata', [
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return [
            'exists'             => $exists,
            'size'               => $size,
            'size_formatted'     => $this->formatBytes($size),
            'modified'           => $modified,
            'modified_formatted' => $modified ? date('Y-m-d H:i:s', $modified) : '-',
            'version'            => $version,
            'path'               => $path,
        ];
    }

    /**
     * Check if database is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return File::exists($this->getDatabasePath());
    }

    /**
     * Resolve the active database path from config('innoshop.geo_lite_path').
     *
     * @return string
     */
    public function getDatabasePath(): string
    {
        return (string) config('innoshop.geo_lite_path');
    }

    /**
     * Format bytes to human readable format
     *
     * @param  int  $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);

        return round($bytes, 2).' '.$units[$pow];
    }
}
