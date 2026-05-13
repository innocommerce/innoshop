<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\RestAPI\PanelApiControllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Services\AI\AIServiceManager;
use InnoShop\Common\Services\AI\ProviderRegistry;
use InnoShop\Panel\Controllers\BaseController;

class AIImageController extends BaseController
{
    /**
     * Get current AI image model info.
     */
    public function modelsInfo(): mixed
    {
        try {
            $registry = app(ProviderRegistry::class);
            $registry->buildLaravelAiConfig();

            $imageProvider = config('ai.default_for_images', config('ai.default'));
            $imageModel    = config("ai.providers.{$imageProvider}.models.image.default", '');
            $providerName  = ucfirst($imageProvider);

            $label = $providerName;
            if ($imageModel) {
                $label .= ' / '.$imageModel;
            }

            return json_success('success', ['image_model' => $label]);
        } catch (Exception $e) {
            return json_success('success', ['image_model' => '']);
        }
    }

    /**
     * Resolve a storage key, local path, or URL to a local file path.
     * Returns null if the file cannot be resolved.
     */
    protected function resolveLocalImagePath(string $reference): ?string
    {
        $mediaDisk = Storage::disk('media');

        // Already a local file path
        if (file_exists($reference)) {
            return $reference;
        }

        // Storage key like "static/media/logos/xxx.png" or "logos/xxx.png"
        if ($mediaDisk->exists($reference)) {
            return $mediaDisk->path($reference);
        }

        // Strip common prefix if present
        $stripped = preg_replace('#^static/media/#', '', $reference);
        if ($stripped !== $reference && $mediaDisk->exists($stripped)) {
            return $mediaDisk->path($stripped);
        }

        // Fallback: URL → extract path
        if (str_starts_with($reference, 'http')) {
            $decoded = urldecode($reference);
            $path    = preg_replace('#^https?://[^/]+#', '', $decoded);
            if ($path) {
                $key = preg_replace('#^/static/media/#', '', $path);
                $key = ltrim($key, '/');
                if ($mediaDisk->exists($key)) {
                    return $mediaDisk->path($key);
                }
            }
        }

        return null;
    }

    /**
     * Generate image from text prompt.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function generate(Request $request): mixed
    {
        set_time_limit(300);
        try {
            $prompt = $request->input('prompt', '');
            if (empty($prompt)) {
                throw new Exception('Empty prompt');
            }

            $options = [
                'size'      => $request->input('size'),
                'quality'   => $request->input('quality'),
                'save_path' => $request->input('save_path', 'ai-images'),
            ];

            $referenceImage = $request->input('reference_image');
            if ($referenceImage) {
                $options['reference_image'] = $this->resolveLocalImagePath($referenceImage);
            } else {
                unset($options['reference_image']);
            }

            $provider = $request->input('provider');
            $model    = $request->input('model');
            if ($provider) {
                $options['provider'] = $provider;
            }
            if ($model) {
                $options['model'] = $model;
            }

            $manager = AIServiceManager::getInstance();
            $result  = $manager->generateImage($prompt, $options);

            Log::info('AI Image generated successfully', ['path' => $result['path']]);

            return json_success('success', $result);
        } catch (Exception $e) {
            Log::error('AI Image generation failed: '.$e->getMessage());

            return json_fail($e->getMessage());
        }
    }
}
