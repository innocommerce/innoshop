<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

class StorageService
{
    /**
     * Path prefix that identifies a storage file in the database.
     * Database values starting with this prefix are treated as uploaded storage files;
     * everything else is treated as a local asset (images/, static/themes/, etc.).
     */
    public const STORAGE_PREFIX = 'static/media/';

    protected array $config;

    public function __construct()
    {
        $this->config = $this->loadConfig();
    }

    /**
     * Get the singleton instance (cached per request via app singleton).
     */
    public static function getInstance(): static
    {
        return app(static::class);
    }

    /**
     * Prepend the storage prefix to a bare storage key.
     */
    public static function storageKey(string $key): string
    {
        return self::STORAGE_PREFIX.ltrim($key, '/');
    }

    /**
     * Strip the storage prefix from a path, returning the bare key.
     */
    public static function stripPrefix(string $path): string
    {
        $clean = ltrim($path, '/');

        return str_starts_with($clean, self::STORAGE_PREFIX)
            ? substr($clean, strlen(self::STORAGE_PREFIX))
            : $clean;
    }

    /**
     * Check if a path is a storage file (has the prefix).
     */
    public static function isStoragePath(string $path): bool
    {
        return str_starts_with(ltrim($path, '/'), self::STORAGE_PREFIX);
    }

    /**
     * Generate the full URL for a storage path.
     */
    public function url(?string $path): string
    {
        $baseUrl = $this->getBaseUrl();

        if (empty($path)) {
            return $baseUrl;
        }

        if (str_starts_with($path, 'http')) {
            return $path;
        }

        $cleanPath = ltrim($path, '/');

        if (! str_starts_with($cleanPath, self::STORAGE_PREFIX)) {
            return asset($cleanPath);
        }

        $key = substr($cleanPath, strlen(self::STORAGE_PREFIX));

        if (! $this->config['is_s3']) {
            return asset(self::STORAGE_PREFIX.$key);
        }

        return $this->signUrl($baseUrl.'/'.$key);
    }

    /**
     * Get the base URL for the current storage driver.
     */
    public function getBaseUrl(): string
    {
        if (! $this->config['is_s3']) {
            return asset(rtrim(self::STORAGE_PREFIX, '/'));
        }

        if ($this->config['cdn_domain']) {
            return rtrim($this->config['cdn_domain'], '/');
        }

        $endpoint = preg_replace('#^https?://#', '', $this->config['endpoint']);

        return sprintf('https://%s.%s', $this->config['bucket'], $endpoint);
    }

    /**
     * Resize an image path, returning a URL.
     */
    public function resize(?string $image, int $width = 100, int $height = 100, ?string $mode = null): string
    {
        if (empty($image)) {
            return (new ImageService(''))->resize($width, $height, $mode);
        }

        if (str_starts_with($image, 'http')) {
            $ossMode = $this->mapResizeMode($mode);

            return $image.'?x-oss-process=image/resize,m_'.$ossMode.',w_'.$width.',h_'.$height;
        }

        $cleanPath = ltrim($image, '/');

        if (! str_starts_with($cleanPath, self::STORAGE_PREFIX)) {
            return (new ImageService($image))->resize($width, $height, $mode);
        }

        $key = substr($cleanPath, strlen(self::STORAGE_PREFIX));

        if (! $this->config['is_s3']) {
            return (new ImageService(self::STORAGE_PREFIX.$key))->resize($width, $height, $mode);
        }

        $url     = $this->url($image);
        $ossMode = $this->mapResizeMode($mode);
        $driver  = $this->config['driver'];

        if ($driver === 'oss') {
            $sep = str_contains($url, '?') ? '&' : '?';

            return $url.$sep.'x-oss-process=image/resize,m_'.$ossMode.',w_'.$width.',h_'.$height;
        }

        if ($driver === 'qiniu') {
            $sep = str_contains($url, '?') ? '&' : '?';

            return $url.$sep.'imageView2/1/w/'.$width.'/h/'.$height;
        }

        return $url;
    }

    protected function mapResizeMode(?string $mode): string
    {
        return match ($mode) {
            'contain', 'pad' => 'pad',
            default => 'fill',
        };
    }

    protected function loadConfig(): array
    {
        $driver    = system_setting('file_manager_driver', 'local');
        $s3Drivers = ['oss', 'cos', 'qiniu', 's3', 'obs', 'r2', 'minio'];

        if (in_array($driver, $s3Drivers)) {
            $prefix = "storage_{$driver}_";

            return [
                'driver'     => $driver,
                'is_s3'      => true,
                'key'        => system_setting($prefix.'key', system_setting('storage_key', '')),
                'secret'     => system_setting($prefix.'secret', system_setting('storage_secret', '')),
                'bucket'     => system_setting($prefix.'bucket', system_setting('storage_bucket', '')),
                'endpoint'   => system_setting($prefix.'endpoint', system_setting('storage_endpoint', '')),
                'cdn_domain' => system_setting($prefix.'cdn_domain', system_setting('storage_cdn_domain', '')),
            ];
        }

        return ['driver' => 'local', 'is_s3' => false];
    }

    protected function signUrl(string $url): string
    {
        if ($this->config['driver'] === 'qiniu' && $this->config['cdn_domain']) {
            $deadline    = time() + 3600;
            $urlToSign   = $url.'?e='.$deadline;
            $sign        = hash_hmac('sha1', $urlToSign, $this->config['secret'], true);
            $encodedSign = str_replace(['+', '/'], ['-', '_'], base64_encode($sign));
            $token       = $this->config['key'].':'.$encodedSign;

            return $urlToSign.'&token='.$token;
        }

        return $url;
    }
}
