<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\DevTools\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarketplaceService
{
    private string $baseUrl;

    private \Illuminate\Http\Client\PendingRequest $client;

    public function __construct()
    {
        if (! defined('CURL_SSLVERSION_TLSv1_2')) {
            define('CURL_SSLVERSION_TLSv1_2', 6);
        }

        $domainToken   = system_setting('domain_token');
        $this->baseUrl = config('innoshop.api_url').'/api/marketplace';

        // Get current locale code
        $locale = function_exists('panel_locale_code') ? panel_locale_code() : (function_exists('locale_code') ? locale_code() : 'en');

        $this->client = Http::baseUrl($this->baseUrl)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'domain-token' => $domainToken,
                'locale'       => $locale,
            ]);
    }

    /**
     * Upload plugin or theme to marketplace.
     *
     * @param  string  $zipPath
     * @param  string  $type
     * @param  array  $metadata
     * @return array
     * @throws ConnectionException
     * @throws Exception
     */
    public function upload(string $zipPath, string $type, array $metadata = []): array
    {
        if (! in_array($type, ['plugin', 'theme'])) {
            throw new Exception('Invalid product type! Must be "plugin" or "theme"');
        }

        if (! file_exists($zipPath)) {
            throw new Exception("Package file does not exist: {$zipPath}");
        }

        $uri = '/products/upload';
        $this->log('upload', ['uri' => $uri, 'type' => $type, 'zipPath' => $zipPath]);

        // Read config.json from metadata if provided
        $config = $metadata['config'] ?? [];

        // Prepare multipart form data
        $response = $this->client->asMultipart()
            ->attach('package', file_get_contents($zipPath), basename($zipPath))
            ->attach('type', $type)
            ->attach('code', $config['code'] ?? '')
            ->attach('version', $config['version'] ?? 'v1.0.0')
            ->post($uri);

        $result = $this->response($response);

        if (isset($result['error'])) {
            throw new Exception($result['error']);
        }

        return $result;
    }

    /**
     * Handle response.
     *
     * @param  \Illuminate\Http\Client\Response  $response
     * @return mixed
     */
    private function response(\Illuminate\Http\Client\Response $response): mixed
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

    /**
     * Log message.
     *
     * @param  string  $action
     * @param  array  $data
     * @return void
     */
    private function log(string $action, array $data): void
    {
        Log::debug("MarketplaceService::{$action}", $data);
    }
}
