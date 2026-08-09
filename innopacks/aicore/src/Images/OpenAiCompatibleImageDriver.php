<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Images;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Services\StorageService;
use RuntimeException;

/**
 * Default image driver for OpenAI-compatible /v1/images/generations endpoints.
 *
 * Vendors known to work: OpenAI, GLM (智谱 CogView), Azure OpenAI,
 * Gemini via /v1beta/openai proxy. Vendors with non-standard image APIs
 * (MiniMax /v1/image_generation, Qianwen DashScope, Doubao async) are not
 * supported here — they need a vendor-specific plugin that registers via
 * the `ai.image_generate_driver` hook with higher priority.
 */
class OpenAiCompatibleImageDriver
{
    /**
     * Generate an image via POST {base_url}/images/generations.
     *
     * @param  string  $prompt
     * @param  array  $options  {provider, model, size, quality, save_path, ...}
     * @return array {name, path, url, origin_url}
     */
    public function generate(string $prompt, array $options = []): array
    {
        $provider       = $options['provider'] ?? config('ai.default_for_images', 'openai');
        $providerConfig = config("ai.providers.{$provider}", []);
        if (empty($providerConfig)) {
            throw new RuntimeException("AI provider [{$provider}] is not configured.");
        }

        $baseUrl = rtrim($providerConfig['url'] ?? $providerConfig['base_url'] ?? '', '/');
        $apiKey  = $providerConfig['key'] ?? '';
        $model   = $options['model'] ?? ($providerConfig['models']['image']['default'] ?? '');

        if (! $apiKey) {
            $providerName = $providerConfig['name'] ?? ucfirst($provider);
            throw new RuntimeException("AI provider [{$providerName}] has no API key configured.");
        }

        if (! $model) {
            $providerName = $providerConfig['name'] ?? ucfirst($provider);
            throw new RuntimeException("AI provider [{$providerName}] has no image model configured.");
        }

        $payload = array_filter([
            'model'   => $model,
            'prompt'  => $prompt,
            'n'       => 1,
            'size'    => $this->normalizeSize($options['size'] ?? null),
            'quality' => $options['quality'] ?? null,
        ]);

        $response = Http::withToken($apiKey)
            ->timeout(300)
            ->post("{$baseUrl}/images/generations", $payload);

        if (! $response->successful()) {
            $body         = $response->json() ?: [];
            $msg          = $body['error']['message'] ?? $body['message'] ?? $response->body();
            $providerName = $providerConfig['name'] ?? ucfirst($provider);
            throw new RuntimeException("AI image generation failed at {$providerName}: {$msg} (HTTP {$response->status()})");
        }

        $data     = $response->json() ?: [];
        $imageUrl = $data['data'][0]['url'] ?? '';
        $b64      = $data['data'][0]['b64_json'] ?? '';

        if (! $imageUrl && ! $b64) {
            throw new RuntimeException('AI image generation returned no image data.');
        }

        $contents = $b64
            ? base64_decode($b64)
            : Http::get($imageUrl)->body();

        if (! $contents) {
            throw new RuntimeException('AI image generation returned empty image data.');
        }

        $savePath   = trim($options['save_path'] ?? '', '/');
        $filename   = 'ai_'.uniqid().'.png';
        $fullPath   = $savePath ? "{$savePath}/{$filename}" : $filename;
        $storedPath = Storage::disk('media')->put($fullPath, $contents);

        if (! $storedPath) {
            throw new RuntimeException('Failed to store generated image.');
        }

        $storageKey = StorageService::storageKey($fullPath);

        return [
            'name'       => $filename,
            'path'       => $storageKey,
            'url'        => storage_url($storageKey),
            'origin_url' => $imageUrl ?: storage_url($storageKey),
        ];
    }

    /**
     * Normalize size input. Accepts either "1024x1024" or aspect ratio like "1:1".
     * OpenAI-compatible API expects explicit dimensions, so ratios are mapped
     * to common sizes.
     */
    private function normalizeSize(?string $size): ?string
    {
        if (! $size) {
            return null;
        }

        if (preg_match('/^\d+x\d+$/', $size)) {
            return $size;
        }

        return [
            '1:1'  => '1024x1024',
            '3:2'  => '1536x1024',
            '2:3'  => '1024x1536',
            '16:9' => '1792x1024',
            '9:16' => '1024x1792',
        ][$size] ?? null;
    }
}
