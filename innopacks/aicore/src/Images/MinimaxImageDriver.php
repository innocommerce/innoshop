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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Services\StorageService;
use RuntimeException;

/**
 * MiniMax image generation driver.
 *
 * Selects the generation endpoint and response format for the configured model.
 */
class MinimaxImageDriver
{
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
            throw new RuntimeException('MiniMax API key is not configured.');
        }
        if (! $model) {
            throw new RuntimeException('MiniMax image model is not configured (e.g. image-01-1.0).');
        }

        // Route by prefix so future H3 variants (e.g. a preview tier) keep
        // using the OpenAI-compatible endpoint instead of the legacy one.
        if (str_starts_with($model, 'MiniMax-H3-Image')) {
            return $this->generateH3($baseUrl, $apiKey, $model, $prompt, $options);
        }

        $payload = array_filter([
            'model'        => $model,
            'prompt'       => $prompt,
            'aspect_ratio' => $this->normalizeAspectRatio($options['size'] ?? null),
        ]);

        // Image-to-image: subject_reference[].image_file accepts either a
        // public URL or a data URL (base64). For local-only files we inline
        // base64; for publicly reachable storage (S3/COS/OSS) we pass the URL
        // so MiniMax fetches it directly and we save the request size.
        if (! empty($options['reference_image'])) {
            $referenceUrl = $this->resolveReferenceUrl($options['reference_image']);
            if ($referenceUrl) {
                $payload['subject_reference'] = [[
                    'type'       => 'character',
                    'image_file' => $referenceUrl,
                ]];
            } else {
                $dataUrl = $this->localFileToDataUrl($options['reference_image']);
                if ($dataUrl) {
                    $payload['subject_reference'] = [[
                        'type'       => 'character',
                        'image_file' => $dataUrl,
                    ]];
                    Log::info('MiniMax reference image sent as base64 data URL');
                } else {
                    Log::warning('MiniMax image-to-image skipped: reference image not readable', [
                        'reference' => $options['reference_image'],
                    ]);
                }
            }
        }

        $response = Http::withToken($apiKey)
            ->timeout(300)
            ->post("{$baseUrl}/image_generation", $payload);

        Log::info('MiniMax image API response', [
            'url'         => "{$baseUrl}/image_generation",
            'status'      => $response->status(),
            'body_prefix' => substr($response->body(), 0, 200),
        ]);

        if (! $response->successful()) {
            $body = $response->json() ?: [];
            $msg  = $body['base_resp']['status_msg']
                ?? $body['error']['message']
                ?? $body['message']
                ?? $response->body();
            throw new RuntimeException("MiniMax image generation failed: {$msg} (HTTP {$response->status()})");
        }

        $data      = $response->json() ?: [];
        $imageUrls = $data['data']['image_urls']
            ?? $data['data']['images']
            ?? $data['images']['images']
            ?? $data['images']
            ?? [];

        // Extract URLs from array-of-objects shape: [{url: "..."}, ...]
        $imageUrls = array_map(
            fn ($item) => is_array($item) ? ($item['url'] ?? ($item['image_url'] ?? '')) : $item,
            $imageUrls
        );
        $imageUrls = array_filter($imageUrls, fn ($u) => is_string($u) && $u !== '');

        if (empty($imageUrls)) {
            Log::error('MiniMax image generation empty response', [
                'model'  => $model,
                'status' => $data['base_resp']['status_code'] ?? null,
                'msg'    => $data['base_resp']['status_msg'] ?? null,
                'keys'   => array_keys($data),
            ]);
            throw new RuntimeException(
                'MiniMax returned no image data. '
                .($data['base_resp']['status_msg'] ? 'Reason: '.$data['base_resp']['status_msg'] : '')
                .' (model: '.$model.')'
            );
        }

        $contents = Http::get($imageUrls[0])->body();
        if (! $contents) {
            throw new RuntimeException('Failed to download image from MiniMax.');
        }

        $result = $this->storeImage($contents, $options['save_path'] ?? '', $imageUrls[0]);

        // MiniMax's `subject_reference` is character-consistency reference,
        // not image editing — the generated subject will not match the
        // reference's identity or clothing. Surface this so the UI can warn.
        if (! empty($options['reference_image'])) {
            $result['notice'] = 'MiniMax 仅支持角色一致性参考（生成相同人物形象的新图），无法用于图片编辑或换装。如需保留原图修改衣服/背景，请改用 OpenAI gpt-image-1 等支持图片编辑的模型。';
        }

        return $result;
    }

    private function generateH3(string $baseUrl, string $apiKey, string $model, string $prompt, array $options): array
    {
        if (! empty($options['reference_image'])) {
            throw new RuntimeException('MiniMax H3 generation accepts text prompts only.');
        }
        if ($baseUrl === '' || $baseUrl === 'https://api.minimax.chat/v1') {
            throw new RuntimeException('Set the MiniMax base URL to https://api.minimax.io/v1 or https://api.minimax.cn/v1 for your API key.');
        }

        $payload = [
            'model'           => $model,
            'prompt'          => $prompt,
            'n'               => $options['n'] ?? 1,
            'response_format' => 'b64_json',
            'output_format'   => 'jpeg',
        ];
        $size = $options['size'] ?? null;
        if ($size) {
            $payload[str_contains($size, ':') ? 'aspect_ratio' : 'size'] = $size;
        }

        // H3 generation can legitimately take minutes; operators must raise
        // fastcgi_read_timeout / request_terminate_timeout above 600s or the
        // reverse proxy cuts the request before MiniMax answers.
        $response = Http::withToken($apiKey)
            ->timeout(600)
            ->withoutRedirecting()
            ->post("{$baseUrl}/images/generations", $payload);

        Log::info('MiniMax H3 image API response', [
            'url'         => "{$baseUrl}/images/generations",
            'status'      => $response->status(),
            'body_prefix' => substr($response->body(), 0, 200),
        ]);

        if (! $response->successful()) {
            $message = $response->json('error.message') ?? $response->reason() ?? 'request failed';
            throw new RuntimeException("MiniMax H3 generation failed: {$message} (HTTP {$response->status()})");
        }

        $images = [];
        $errors = [];
        foreach ($response->json('data', []) as $image) {
            if (isset($image['error'])) {
                $errors[] = $image['error']['message'] ?? 'MiniMax H3 image generation failed.';

                continue;
            }
            $contents = base64_decode($image['b64_json'] ?? '', true);
            if ($contents === false || $contents === '') {
                $errors[] = 'MiniMax H3 returned no image data.';

                continue;
            }
            $images[] = $this->storeImage($contents, $options['save_path'] ?? '', extension: 'jpg');
        }
        if (empty($images)) {
            throw new RuntimeException($errors[0] ?? 'MiniMax H3 returned no image data.');
        }

        $result = $images[0];
        if (count($images) > 1) {
            array_unshift($errors, __('aicore::media.ai_more_images_notice'));
        }
        if ($errors) {
            $result['notice'] = implode(' ', array_unique($errors));
        }

        return $result;
    }

    private function storeImage(string $contents, string $savePath, ?string $originUrl = null, string $extension = 'png'): array
    {
        $savePath = trim($savePath, '/');
        $filename = 'ai_'.uniqid().'.'.$extension;
        $fullPath = $savePath ? "{$savePath}/{$filename}" : $filename;
        if (! Storage::disk('media')->put($fullPath, $contents)) {
            throw new RuntimeException('Failed to store generated image.');
        }

        $storageKey = StorageService::storageKey($fullPath);
        $url        = storage_url($storageKey);

        return [
            'name'       => $filename,
            'path'       => $storageKey,
            'url'        => $url,
            'origin_url' => $originUrl ?? $url,
        ];
    }

    private function normalizeAspectRatio(?string $size): ?string
    {
        if (! $size) {
            return null;
        }

        // Already an aspect ratio like "1:1"
        if (preg_match('/^\d+:\d+$/', $size)) {
            return $size;
        }

        return [
            '1024x1024' => '1:1',
            '1536x1024' => '3:2',
            '1024x1536' => '2:3',
            '1792x1024' => '16:9',
            '1024x1792' => '9:16',
        ][$size] ?? null;
    }

    /**
     * Resolve a reference image path/storage key to a publicly accessible URL.
     * Returns null if the URL points to a local/private host that MiniMax
     * cannot fetch (e.g. bundle.test, 192.168.x.x, localhost).
     */
    private function resolveReferenceUrl(string $reference): ?string
    {
        $url = str_starts_with($reference, 'http')
            ? $reference
            : storage_url($reference);

        $host = strtolower((string) parse_url($url, PHP_URL_HOST));
        if ($host === '') {
            return null;
        }

        // Reject obviously local hosts
        if (in_array($host, ['localhost', '127.0.0.1'], true)
            || str_ends_with($host, '.test')
            || str_ends_with($host, '.local')
            || preg_match('/^192\.168\./', $host)
            || preg_match('/^10\./', $host)
            || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $host)
        ) {
            return null;
        }

        return $url;
    }

    /**
     * Read a local reference image and return it as a data: URL (base64).
     * Used when the storage is not publicly reachable (local dev, private host).
     */
    private function localFileToDataUrl(string $reference): ?string
    {
        $localPath = $this->resolveLocalPath($reference);
        if (! $localPath || ! is_readable($localPath)) {
            return null;
        }

        $contents = file_get_contents($localPath);
        if ($contents === false) {
            return null;
        }

        $mime = mime_content_type($localPath) ?: 'image/jpeg';

        return 'data:'.$mime.';base64,'.base64_encode($contents);
    }

    /**
     * Resolve a reference path to a local filesystem path.
     * Handles absolute paths, storage keys like "static/media/red.jpg",
     * and bare paths relative to the media disk.
     */
    private function resolveLocalPath(string $reference): ?string
    {
        if (is_file($reference)) {
            return $reference;
        }

        $disk = Storage::disk('media');
        $key  = preg_replace('#^static/media/#', '', ltrim($reference, '/'));

        if ($disk->exists($reference)) {
            return $disk->path($reference);
        }
        if ($disk->exists($key)) {
            return $disk->path($key);
        }

        return null;
    }
}
