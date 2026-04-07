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
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InnoShop\Plugin\Traits\CleansUpExtractedFiles;
use PhpZip\Exception\ZipException;

class MarketplaceService
{
    use CleansUpExtractedFiles;

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
     * Get product detail cache TTL (shorter for version freshness)
     *
     * @return int
     */
    private function getProductDetailCacheTtl(): int
    {
        return (int) system_setting('marketplace_product_detail_cache_ttl', 300); // 默认 5 分钟
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
     * @return Repository
     */
    private function getCacheStore(array $tags = []): Repository
    {
        if ($this->cacheSupportsTags() && ! empty($tags)) {
            return Cache::tags($tags);
        }

        return Cache::store();
    }

    /**
     * Build cache key with prefix and API URL hash
     *
     * @param  string  $key
     * @return string
     */
    private function buildCacheKey(string $key): string
    {
        // Include API URL hash to ensure cache is invalidated when API URL changes
        $apiUrlHash = md5(config('innoshop.api_url'));

        return 'marketplace.'.$apiUrlHash.'.'.$key;
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
     * @param  int  $limit  Limit the number of categories returned (0 = no limit)
     * @return mixed
     * @throws ConnectionException
     */
    public function getPluginCategories(int $limit = 0): mixed
    {
        return $this->getMarketCategories('plugins', $limit);
    }

    /**
     * @param  int  $limit  Limit the number of categories returned (0 = no limit)
     * @return mixed
     * @throws ConnectionException
     */
    public function getThemeCategories(int $limit = 0): mixed
    {
        return $this->getMarketCategories('themes', $limit);
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
     * @param  bool  $forceRefresh  Force refresh cache to get latest version
     * @return mixed
     * @throws ConnectionException
     */
    public function getProductDetail($id, bool $forceRefresh = false): mixed
    {
        $cacheKey = $this->buildCacheKey("product.detail.{$id}");

        // If force refresh, clear cache first
        if ($forceRefresh) {
            $this->clearProductCache($id);
        }

        if ($this->isCacheEnabled() && ! $forceRefresh) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cached     = $cacheStore->get($cacheKey);
            if ($cached !== null) {
                $this->log('getProductDetail (cached)', ['id' => $id]);

                return $cached;
            }
        }

        $uri = '/products/'.$id;
        $this->log('getProductDetail', ['uri' => $uri, 'id' => $id, 'force_refresh' => $forceRefresh]);

        $response = $this->client->get($uri);
        $result   = $this->response($response);

        // Use shorter cache TTL for product detail (for version freshness)
        if ($this->isCacheEnabled() && ! isset($result['error'])) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cacheStore->put($cacheKey, $result, $this->getProductDetailCacheTtl());
        }

        return $result;
    }

    /**
     * Clear cache for a specific product
     *
     * @param  int  $productId
     * @return void
     */
    public function clearProductCache(int $productId): void
    {
        $cacheKey = $this->buildCacheKey("product.detail.{$productId}");

        if ($this->cacheSupportsTags()) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market', 'theme_market']);
            $cacheStore->forget($cacheKey);
        } else {
            Cache::forget($cacheKey);
        }

        $this->log('Product cache cleared', ['product_id' => $productId]);
    }

    /**
     * Clear all marketplace cache
     *
     * @return void
     */
    public function clearAllCache(): void
    {
        if ($this->cacheSupportsTags()) {
            Cache::tags(['marketplace', 'plugin_market', 'theme_market'])->flush();
        } else {
            // For drivers that don't support tags, clear all marketplace.* keys
            // This is a best-effort approach
            $this->log('clearAllCache: Cache driver does not support tags, manual cleanup may be needed');
        }

        $this->log('All marketplace cache cleared');
    }

    /**
     * Get market categories.
     *
     * @param  $parentSlug
     * @param  int  $limit  Limit the number of categories returned
     * @return mixed
     * @throws ConnectionException
     */
    private function getMarketCategories($parentSlug, int $limit = 0): mixed
    {
        $cacheKey = $this->buildCacheKey("categories.{$parentSlug}.{$limit}");

        if ($this->isCacheEnabled()) {
            $cacheStore = $this->getCacheStore(['marketplace', 'plugin_market']);
            $cached     = $cacheStore->get($cacheKey);
            if ($cached !== null) {
                $this->log('getMarketCategories (cached)', ['parentSlug' => $parentSlug, 'limit' => $limit]);

                return $cached;
            }
        }

        $uri = "/categories?parent_slug=$parentSlug";
        if ($limit > 0) {
            $uri .= "&limit=$limit";
        }
        $this->log('getMarketCategories', ['uri' => $uri, 'parentSlug' => $parentSlug, 'limit' => $limit]);

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
            throw new Exception('Invalid product type!');
        }

        // Clear product cache before download to ensure we get the latest version
        $this->clearProductCache((int) $id);

        $uri = "/products/$id/download";
        $this->log('download', ['uri' => $uri, 'id' => $id, 'type' => $type]);

        $datetime = date('Y-m-d');

        $content = $this->client->get($uri)->body();

        $pluginPath = "plugins/$id-$datetime.zip";
        Storage::disk('local')->put($pluginPath, $content);

        $pluginZip = storage_path('app/'.$pluginPath);

        $destinationRoot = $type === 'plugin' ? base_path('plugins') : base_path('themes');
        $this->extractZipAndMergeIntoRoot($pluginZip, $destinationRoot);
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
