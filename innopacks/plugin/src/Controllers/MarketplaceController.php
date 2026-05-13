<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Plugin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use InnoShop\Common\Models\Setting;
use InnoShop\Common\Repositories\SettingRepo;
use InnoShop\Plugin\Services\MarketplaceService;
use Throwable;

class MarketplaceController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function quickCheckout(Request $request): mixed
    {
        try {
            $data = $request->all();

            return MarketplaceService::getInstance()->quickCheckout($data);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  int  $number
     * @return mixed
     */
    public function checkOrderStatus(int $number): mixed
    {
        try {
            return MarketplaceService::getInstance()->checkOrderStatus($number);
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function updateDomainToken(Request $request): mixed
    {
        try {
            $domainToken = $request->get('domain_token');
            SettingRepo::getInstance()->updateSystemValue('domain_token', $domainToken);

            return json_success(common_trans('base.updated_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @return mixed
     * @throws Throwable
     */
    public function updateAuthToken(Request $request): mixed
    {
        try {
            $authToken = $request->get('auth_token');
            if ($authToken) {
                SettingRepo::getInstance()->updateSystemValue('marketplace_auth_token', $authToken);
            } else {
                Setting::query()->where('space', 'system')->where('name', 'marketplace_auth_token')->delete();
            }

            return json_success(common_trans('base.updated_success'));
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * @param  Request  $request
     * @param  int  $slug
     * @return mixed
     */
    public function download(Request $request, int $slug): mixed
    {
        try {
            $type = $request->get('type', 'plugin');
            MarketplaceService::getInstance()->download($slug, $type);

            return json_success('下载成功, 请去插件或主题列表安装使用');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Get domain token from API
     *
     * @return mixed
     */
    public function getToken(): mixed
    {
        try {
            $baseUrl  = config('innoshop.api_url');
            $response = Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->get('/api/domains/token');

            if ($response->successful()) {
                $data  = $response->json();
                $token = $data['data']['token'] ?? $data['data']['domain_token'] ?? null;

                if ($token) {
                    SettingRepo::getInstance()->updateSystemValue('domain_token', $token);
                }

                return json_success(trans('common/base.success'), ['token' => $token]);
            }

            return json_fail($response->json()['message'] ?? '获取 token 失败');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Proxy billing methods from innoshop.cn API to avoid CORS issues.
     *
     * @return mixed
     */
    public function billingMethods(): mixed
    {
        try {
            $baseUrl  = config('innoshop.api_url');
            $response = Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->get('/api/checkout/billing_methods');

            if ($response->successful()) {
                $data    = $response->json();
                $methods = is_array($data) && isset($data['data']) ? $data['data'] : $data;

                return response()->json($methods);
            }

            return json_fail($response->json()['message'] ?? 'Failed to fetch billing methods');
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Proxy login request to innoshop.cn API.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function proxyLogin(Request $request): mixed
    {
        try {
            $baseUrl  = config('innoshop.api_url');
            $response = Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->post('/api/login', $request->only('email', 'password'));

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Proxy register request to innoshop.cn API.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function proxyRegister(Request $request): mixed
    {
        try {
            $baseUrl  = config('innoshop.api_url');
            $response = Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->post('/api/register', $request->only('email', 'password', 'password_confirmation'));

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Proxy domain bind request to innoshop.cn API.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function proxyBindDomain(Request $request): mixed
    {
        try {
            $baseUrl   = config('innoshop.api_url');
            $headers   = [];
            $authToken = $request->input('auth_token') ?: system_setting('marketplace_auth_token');
            if ($authToken) {
                $headers['Authorization'] = 'Bearer '.$authToken;
            }
            $response = Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->withHeaders($headers)
                ->post('/api/marketplace/domains/bind', [
                    'domain' => $request->input('domain', $request->getHost()),
                ]);

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Proxy account/me request to innoshop.cn API.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function proxyAccountMe(Request $request): mixed
    {
        try {
            $baseUrl   = config('innoshop.api_url');
            $authToken = $request->input('auth_token') ?: system_setting('marketplace_auth_token');
            $headers   = [];
            if ($authToken) {
                $headers['Authorization'] = 'Bearer '.$authToken;
            }
            $response = Http::baseUrl($baseUrl)
                ->withOptions(['verify' => false])
                ->withHeaders($headers)
                ->get('/api/account/me');

            return response()->json($response->json(), $response->status());
        } catch (\Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Clear marketplace cache
     *
     * @return mixed
     */
    public function clearCache(): mixed
    {
        try {
            $driver           = config('cache.default');
            $supportedDrivers = ['redis', 'memcached', 'dynamodb'];

            if (in_array($driver, $supportedDrivers)) {
                // Use tags if supported
                try {
                    Cache::tags(['marketplace', 'plugin_market', 'theme_market'])->flush();
                } catch (\Exception $e) {
                    // Fallback if tags still fail
                    Log::warning('Cache tags flush failed, using prefix method', [
                        'driver' => $driver,
                        'error'  => $e->getMessage(),
                    ]);
                    $this->clearCacheByPrefix('marketplace.');
                }
            } else {
                // For drivers that don't support tags, clear by prefix
                $this->clearCacheByPrefix('marketplace.');
            }

            return json_success(trans('common/base.success'));
        } catch (\Exception $e) {
            Log::error('Failed to clear marketplace cache', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Clear cache by prefix (for drivers that don't support tags)
     *
     * @param  string  $prefix
     * @return void
     */
    private function clearCacheByPrefix(string $prefix): void
    {
        $driver = config('cache.default');

        try {
            if ($driver === 'database') {
                // For database driver, delete from cache table
                $cacheTable  = config('cache.stores.database.table', 'cache');
                $cachePrefix = config('cache.prefix', '');

                // Check if table exists
                if (! Schema::hasTable($cacheTable)) {
                    Log::warning('Cache table does not exist', ['table' => $cacheTable]);

                    return;
                }

                // Laravel cache keys are stored with prefix, build the full prefix
                $fullPrefix = $cachePrefix ? $cachePrefix.$prefix : $prefix;

                // Try to delete by prefix pattern
                $deleted = DB::table($cacheTable)
                    ->where('key', 'like', $fullPrefix.'%')
                    ->delete();

                // If no rows deleted, try without prefix (in case prefix is empty or different)
                if ($deleted === 0 && $cachePrefix) {
                    DB::table($cacheTable)
                        ->where('key', 'like', $prefix.'%')
                        ->delete();
                }
            } elseif ($driver === 'file') {
                // For file driver, we need to clear the entire cache directory
                // This is a limitation - file driver doesn't support pattern matching
                // We'll clear all cache as a workaround
                Cache::flush();
            } else {
                // For other drivers, try to flush all cache
                Cache::flush();
            }
        } catch (\Exception $e) {
            // If table doesn't exist or query fails, log the error
            Log::warning('Failed to clear cache by prefix', [
                'driver' => $driver,
                'prefix' => $prefix,
                'error'  => $e->getMessage(),
            ]);
        }
    }
}
