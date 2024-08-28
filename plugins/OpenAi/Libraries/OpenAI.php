<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OpenAi\Libraries;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class OpenAI
{
    private string $baseUrl;

    private PendingRequest $client;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $apiKey = plugin_setting('open_ai', 'api_key');
        if (empty($apiKey)) {
            throw new Exception('Empty OpenAI api_key');
        }

        $this->baseUrl = plugin_setting('open_ai', 'proxy_url', 'https://api.openai.com/v1/');
        $this->client  = Http::baseUrl($this->baseUrl)
            ->withOptions(['verify' => false])
            ->withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer $apiKey",
            ]);
    }

    /**
     * @return self
     */
    public static function getInstance(): OpenAI
    {
        return new self;
    }

    /**
     * @param  $requestData
     * @return mixed
     * @throws ConnectionException
     * @throws Exception
     */
    public function completions($requestData): mixed
    {
        $data     = $this->handleRequestData($requestData);
        $response = $this->client->post('chat/completions', $data);

        return $response->json();
    }

    /**
     * @param  $requestData
     * @return array
     * @throws Exception
     */
    private function handleRequestData($requestData): array
    {
        $origin = $requestData['column_value'];
        $prompt = $this->getPrompt($requestData['column_name']);
        $locale = $requestData['locale_name'] ?? '';
        $suffix = '';
        if ($locale) {
            $suffix = "并用 $locale 返回";
        }

        $content = $origin."\r\n".$prompt."\r\n".$suffix;

        return [
            'model'    => plugin_setting('open_ai', 'model_type'),
            'messages' => [
                [
                    'role'    => 'user',
                    'content' => $content,
                ],
            ],
            'temperature' => 0.7,
        ];
    }

    /**
     * @param  $column
     * @return string
     * @throws Exception
     */
    private function getPrompt($column): string
    {
        $mappings  = $this->getPromptMapping();
        $promptKey = $mappings[$column];
        if (empty($promptKey)) {
            throw new Exception("Empty prompt key for column $column!");
        }

        $prompt = system_setting($promptKey);
        if (empty($prompt)) {
            throw new Exception("Empty prompt value for $promptKey!");
        }

        return $prompt;
    }

    /**
     * @return string[]
     */
    private function getPromptMapping(): array
    {
        return [
            'product_summary'       => 'ai_prompt_product_summary',
            'product_selling_point' => 'ai_prompt_product_selling_point',
            'product_slug'          => 'ai_prompt_product_slug',
            'product_title'         => 'ai_prompt_product_seo_title',
            'product_description'   => 'ai_prompt_product_seo_description',
            'product_keywords'      => 'ai_prompt_product_seo_keywords',

            'article_summary'     => 'ai_prompt_article_summary',
            'article_slug'        => 'ai_prompt_article_slug',
            'article_title'       => 'ai_prompt_article_seo_title',
            'article_description' => 'ai_prompt_article_seo_description',
            'article_keywords'    => 'ai_prompt_article_seo_keywords',
        ];
    }
}
