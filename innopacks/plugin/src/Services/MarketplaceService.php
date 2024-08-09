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
use Illuminate\Support\Facades\Http;
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
        $this->baseUrl = config('innoshop.api_url').'/api/seller';

        $this->client = Http::baseUrl($this->baseUrl)
            ->withOptions(['verify' => false])
            ->withHeaders(['domain-token' => system_setting('domain_token')]);
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
        $uri = '/products/'.$id;

        $response = $this->client->get($uri);

        return $this->response($response);
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
        $uri = "/categories?parent_slug=$parentSlug";

        $response = $this->client->get($uri);

        return $this->response($response);
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

        $response = $this->client->get($uri);

        return $this->response($response);
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
