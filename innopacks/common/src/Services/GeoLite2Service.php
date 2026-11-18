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
     * GeoLite2 database download URL
     */
    private const DOWNLOAD_URL = 'https://res.innoshop.net/GeoLite2-City.mmdb';

    /**
     * A genuine GeoLite2-City database is tens of MB; anything smaller is
     * a stub or a truncated download (e.g. an HTML error page).
     */
    private const MIN_FILE_BYTES = 1048576;

    /**
     * Existing database found by the case-insensitive fallback scan.
     */
    private ?string $fallbackPath = null;

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
     * @return array
     */
    public function downloadDatabase(): array
    {
        try {
            // Download file
            $response = Http::timeout(300)->get(self::DOWNLOAD_URL);

            if (! $response->successful()) {
                return [
                    'success' => false,
                    'message' => __('panel/setting_geolite2.download_failed', [
                        'error' => 'HTTP '.$response->status(),
                    ]),
                ];
            }

            return $this->installFromContent($response->body());
        } catch (Exception $e) {
            Log::error('GeoLite2Service: Failed to download database', [
                'url'   => self::DOWNLOAD_URL,
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
     * Install downloaded database content atomically: validate a temp copy
     * first, then swap it in. The live database is never touched unless the
     * new file passes verification, so a broken download cannot destroy it.
     *
     * @param  string  $content
     * @return array
     */
    private function installFromContent(string $content): array
    {
        if ($content === '') {
            return [
                'success' => false,
                'message' => __('panel/setting_geolite2.download_failed', [
                    'error' => __('panel/setting_geolite2.download_empty'),
                ]),
            ];
        }

        if (strlen($content) < self::MIN_FILE_BYTES) {
            return [
                'success' => false,
                'message' => __('panel/setting_geolite2.download_failed', [
                    'error' => __('panel/setting_geolite2.download_too_small'),
                ]),
            ];
        }

        $path    = $this->getDatabasePath();
        $tmpPath = $path.'.tmp';

        try {
            File::put($tmpPath, $content);

            $reader = new Reader($tmpPath);
            $reader->city('8.8.8.8'); // Test query
            $reader = null;
        } catch (Exception $e) {
            File::delete($tmpPath);

            return [
                'success' => false,
                'message' => __('panel/setting_geolite2.download_failed', [
                    'error' => __('panel/setting_geolite2.verify_failed', ['error' => $e->getMessage()]),
                ]),
            ];
        }

        // Windows rename() cannot overwrite an existing file, so the old
        // database is removed before moving the verified copy in. Both steps
        // are checked: a held file handle (concurrent lookup) must not end up
        // reported as a fake success with .tmp left behind.
        if (File::exists($path) && ! File::delete($path)) {
            File::delete($tmpPath);
            Log::error('GeoLite2Service: Failed to remove old database before swap', ['path' => $path]);

            return [
                'success' => false,
                'message' => __('panel/setting_geolite2.download_failed', [
                    'error' => __('panel/setting_geolite2.swap_failed'),
                ]),
            ];
        }

        if (! File::move($tmpPath, $path)) {
            File::delete($tmpPath);
            Log::error('GeoLite2Service: Failed to move verified database into place', ['path' => $path]);

            return [
                'success' => false,
                'message' => __('panel/setting_geolite2.download_failed', [
                    'error' => __('panel/setting_geolite2.swap_failed'),
                ]),
            ];
        }

        return [
            'success' => true,
            'message' => __('panel/setting_geolite2.download_success'),
            'path'    => $path,
        ];
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
            'path'               => $this->getDisplayPath($path),
            'download_url'       => self::DOWNLOAD_URL,
        ];
    }

    /**
     * Convert an absolute filesystem path to a site-root-relative display path.
     *
     * @param  string  $path
     * @return string
     */
    private function getDisplayPath(string $path): string
    {
        $basePath = base_path();

        if (str_starts_with($path, $basePath)) {
            return substr($path, strlen($basePath));
        }

        return $path;
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
     * The official download is named `GeoLite2-City.mmdb`, but installs that
     * were set up before the default was corrected carry the lower-case
     * `geolite2-city.mmdb`. On a case-sensitive filesystem the configured
     * path alone would never find it, so fall back to a case-insensitive
     * scan of the storage directory.
     *
     * @return string
     */
    public function getDatabasePath(): string
    {
        $configured = (string) config('innoshop.geo_lite_path');

        if ($configured !== '' && File::exists($configured)) {
            return $configured;
        }

        if ($this->fallbackPath !== null && File::exists($this->fallbackPath)) {
            return $this->fallbackPath;
        }

        $this->fallbackPath = $this->findExistingDatabase($configured);

        return $this->fallbackPath ?? $configured;
    }

    /**
     * Look for an existing city database in the directory of the configured
     * path, ignoring the file name case.
     *
     * @param  string  $configuredPath
     * @return string|null
     */
    private function findExistingDatabase(string $configuredPath): ?string
    {
        $directory = dirname($configuredPath);

        if (! File::isDirectory($directory)) {
            return null;
        }

        foreach (File::files($directory) as $file) {
            $name = strtolower($file->getFilename());

            if (str_ends_with($name, '.mmdb') && str_contains($name, 'city')) {
                return $file->getPathname();
            }
        }

        return null;
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
