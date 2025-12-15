<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PhpZip\Exception\ZipException;
use PhpZip\ZipFile;

class MarketplaceService
{
    private string $baseUrl;

    private int $page = 1;

    private int $perPage = 12;

    private PendingRequest $client;

    public function __construct()
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        $domainToken   = system_setting('domain_token');
        $this->baseUrl = config('innoshop.api_url').'/api/marketplace';

        // Get current locale code for Factory backend panel
        $locale = function_exists('panel_locale_code') ? panel_locale_code() : (function_exists('locale_code') ? locale_code() : 'en');

        $this->client = Http::baseUrl($this->baseUrl)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'domain-token' => $domainToken,
                'locale'       => $locale,
            ]);

        // Log initialization
        $this->log('MarketplaceService initialized', [
            'domainToken' => $domainToken,
            'locale'      => $locale,
        ]);
    }

    /**
     * @return self
     */
    public static function getInstance(): MarketplaceService
    {
        return new self;
    }

    /**
     * @param  int  $page
     * @return $this
     */
    public function setPage(int $page): static
    {
        if ($page > 0) {
            $this->page = $page;
        }

        return $this;
    }

    /**
     * @param  int  $perPage
     * @return $this
     */
    public function setPerPage(int $perPage): static
    {
        if ($perPage > 0) {
            $this->perPage = $perPage;
        }

        return $this;
    }

    /**
     * Check if cache is enabled
     *
     * @return bool
     */
    private function isCacheEnabled(): bool
    {
        return (bool) system_setting('marketplace_enable_cache', true);
    }

    /**
     * Get cache TTL in seconds
     *
     * @return int
     */
    private function getCacheTtl(): int
    {
        return (int) system_setting('marketplace_cache_ttl', 3600);
    }

    /**
     * Check if request logging is enabled
     *
     * @return bool
     */
    private function isRequestLogEnabled(): bool
    {
        return (bool) system_setting('marketplace_enable_request_log', true);
    }

    /**
     * Get plugins per page from settings
     *
     * @return int
     */
    private function getPluginsPerPage(): int
    {
        return (int) system_setting('marketplace_plugins_per_page', 12);
    }

    /**
     * Get themes per page from settings
     *
     * @return int
     */
    private function getThemesPerPage(): int
    {
        return (int) system_setting('marketplace_themes_per_page', 12);
    }

    /**
     * Check if current cache store supports tagging
     *
     * @return bool
     */
    private function cacheSupportsTags(): bool
    {
        $driver           = config('cache.default');
        $supportedDrivers = ['redis', 'memcached', 'dynamodb'];

        return in_array($driver, $supportedDrivers);
    }

    /**
     * Get cache instance with or without tags
     *
     * @param  array  $tags
     * @return \Illuminate\Contracts\Cache\Repository
     */
    private function getCacheStore(array $tags = []): \Illuminate\Contracts\Cache\Repository
    {
        if ($this->cacheSupportsTags() && ! empty($tags)) {
            return Cache::tags($tags);
        }

        return Cache::store();
    }

    /**
     * Build cache key with prefix
     *
     * @param  string  $key
     * @return string
     */
    private function buildCacheKey(string $key): string
    {
        return 'marketplace.'.$key;
    }

    /**
     * Log message if request logging is enabled
     *
     * @param  string  $message
     * @param  array  $context
     * @param  string  $level
     * @return void
     */
    private function log(string $message, array $context = [], string $level = 'info'): void
    {
        if (! $this->isRequestLogEnabled()) {
            return;
        }

        $logContext = array_merge([
            'service' => 'MarketplaceService',
            'baseUrl' => $this->baseUrl,
        ], $context);

        Log::{$level}($message, $logContext);
    }

    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function getPluginCategories(): mixed
    {
        return $this->getMarketCategories('plugins');
    }

    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function getThemeCategories(): mixed
    {
        return $this->getMarketCategories('themes');
    }

    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function getPluginProducts(): mixed
    {
        return $this->getMarketProducts('plugins');
    }

    /**
     * @return mixed
     * @throws ConnectionException
     */
    public function getThemeProducts(): mixed
    {
        return $this->getMarketProducts('themes');
    }

    /**
     * @param  $id
     * @return mixed
     * @throws ConnectionException
     */
    public function getProductDetail($id): mixed
    {
        $cacheKey = $this->buildCacheKey("product.detail.{$id}");

        if ($this->isCacheEnabled()) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cached     = $cacheStore->get($cacheKey);
            if ($cached !== null) {
                $this->log('getProductDetail (cached)', ['id' => $id]);

                return $cached;
            }
        }

        $uri = '/products/'.$id;
        $this->log('getProductDetail', ['uri' => $uri, 'id' => $id]);

        $response = $this->client->get($uri);
        $result   = $this->response($response);

        if ($this->isCacheEnabled() && ! isset($result['error'])) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cacheStore->put($cacheKey, $result, $this->getCacheTtl());
        }

        return $result;
    }

    /**
     * Get market categories.
     *
     * @param  $parentSlug
     * @return mixed
     * @throws ConnectionException
     */
    private function getMarketCategories($parentSlug): mixed
    {
        $cacheKey = $this->buildCacheKey("categories.{$parentSlug}");

        if ($this->isCacheEnabled()) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market']);
            $cached     = $cacheStore->get($cacheKey);
            if ($cached !== null) {
                $this->log('getMarketCategories (cached)', ['parentSlug' => $parentSlug]);

                return $cached;
            }
        }

        $uri = "/categories?parent_slug=$parentSlug";
        $this->log('getMarketCategories', ['uri' => $uri, 'parentSlug' => $parentSlug]);

        $response = $this->client->get($uri);
        $result   = $this->response($response);

        if ($this->isCacheEnabled() && ! isset($result['error'])) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market']);
            $cacheStore->put($cacheKey, $result, $this->getCacheTtl());
        }

        return $result;
    }

    /**
     * Get market products.
     *
     * @param  $categorySlug
     * @return mixed
     * @throws ConnectionException
     */
    public function getMarketProducts($categorySlug): mixed
    {
        $uri = "/products?category_slug=$categorySlug&page=$this->page&per_page=$this->perPage";
        $this->log('getMarketProducts', ['uri' => $uri, 'categorySlug' => $categorySlug]);

        $response = $this->client->get($uri);

        return $this->response($response);
    }

    /**
     * Get market products with custom parameters.
     *
     * @param  array  $params
     * @return mixed
     * @throws ConnectionException
     */
    public function getMarketProductsWithParams(array $params): mixed
    {
        // Determine per_page based on product type
        $parentSlug = $params['parent_slug'] ?? '';
        if ($parentSlug === 'plugins' && $this->perPage === 12) {
            $this->perPage = $this->getPluginsPerPage();
        } elseif ($parentSlug === 'themes' && $this->perPage === 12) {
            $this->perPage = $this->getThemesPerPage();
        }

        $queryParams = array_merge([
            'page'     => $this->page,
            'per_page' => $this->perPage,
        ], $params);

        $cacheKey = $this->buildCacheKey('products.'.md5(http_build_query($queryParams)));

        if ($this->isCacheEnabled()) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cached     = $cacheStore->get($cacheKey);
            if ($cached !== null) {
                $this->log('getMarketProductsWithParams (cached)', ['params' => $params]);

                return $cached;
            }
        }

        $uri = '/products?'.http_build_query($queryParams);
        $this->log('getMarketProductsWithParams', ['uri' => $uri, 'params' => $params]);

        $response = $this->client->get($uri);
        $result   = $this->response($response);

        if ($this->isCacheEnabled() && ! isset($result['error'])) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cacheStore->put($cacheKey, $result, $this->getCacheTtl());
        }

        return $result;
    }

    /**
     * Get market products.
     *
     * @param  $data
     * @return mixed
     * @throws ConnectionException
     */
    public function quickCheckout($data): mixed
    {
        $uri = '/checkout/quick_confirm';
        $this->log('quickCheckout', ['uri' => $uri, 'data' => $data]);

        $response = $this->client->post($uri, $data);

        return $this->response($response);
    }

    /**
     * Download plugin from API and extract.
     *
     * @param  $id
     * @param  $type
     * @throws ConnectionException
     * @throws ZipException
     * @throws Exception
     */
    public function download($id, $type): void
    {
        if (! in_array($type, ['plugin', 'theme'])) {
            throw new \Exception('Invalid product type!');
        }

        $uri = "/products/$id/download";
        $this->log('download', ['uri' => $uri, 'id' => $id, 'type' => $type]);

        $datetime = date('Y-m-d');

        $content = $this->client->get($uri)->body();

        $pluginPath = "plugins/$id-$datetime.zip";
        Storage::disk('local')->put($pluginPath, $content);

        $pluginZip = storage_path('app/'.$pluginPath);
        $zipFile   = new ZipFile;

        if ($type == 'plugin') {
            $zipFile->openFile($pluginZip)->extractTo(base_path('plugins'));
        } else {
            $zipFile->openFile($pluginZip)->extractTo(base_path('themes'));
        }
    }

    /**
     * @param  Response  $response
     * @return mixed
     */
    private function response(Response $response): mixed
    {
        $this->log('response', [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);

        if ($response->status() == 200) {
            return $response->json();
        }

        $result = $response->json();
        if (is_null($result)) {
            $error = 'empty response';
        } elseif (is_array($result)) {
            $error = $result['message'] ?? 'unknown error';
        } else {
            $error = 'something wrong';
        }

        return [
            'error' => $error,
        ];
    }
}
