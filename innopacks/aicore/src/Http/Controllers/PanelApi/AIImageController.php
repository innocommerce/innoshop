<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Http\Controllers\PanelApi;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnoShop\Aicore\Services\AIServiceManager;
use InnoShop\Aicore\Services\MediaImageResolver;
use InnoShop\Aicore\Services\ProviderRegistry;
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
            $providerName  = config("ai.providers.{$imageProvider}.name", ucfirst($imageProvider));

            $label = $imageModel ? $providerName.' / '.$imageModel : $providerName.' (no image model)';

            return json_success('success', ['image_model' => $label]);
        } catch (Exception $e) {
            return json_success('success', ['image_model' => '']);
        }
    }

    /**
     * Generate image from text prompt.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function generate(Request $request): mixed
    {
        set_time_limit(660);
        try {
            $prompt = trim((string) $request->input('prompt', ''));
            if ($prompt === '' || mb_strlen($prompt) > 4000) {
                throw new Exception('Prompt must contain between 1 and 4000 characters');
            }

            $savePath = trim((string) $request->input('save_path', 'ai-images'), '/');
            if ($savePath === '' || preg_match('#(^|/)\.\.(/|$)#', $savePath) || str_contains($savePath, "\0")) {
                throw new Exception('Invalid image save path');
            }

            $options = [
                'size'      => $request->input('size'),
                'quality'   => $request->input('quality'),
                'n'         => min(4, max(1, (int) $request->input('count', 1))),
                'save_path' => $savePath,
            ];

            $negativePrompt = trim((string) $request->input('negative_prompt', ''));
            if (mb_strlen($negativePrompt) > 1000) {
                throw new Exception('Negative prompt must not exceed 1000 characters');
            }
            if ($negativePrompt !== '') {
                $options['negative_prompt'] = $negativePrompt;
            }

            $referenceImage = $request->input('reference_image');
            if ($referenceImage) {
                $options['reference_image'] = MediaImageResolver::getInstance()->resolveLocalImagePath((string) $referenceImage);
                if ($options['reference_image'] === null) {
                    throw new Exception('Invalid reference image');
                }
            }

            $provider = $request->input('provider');
            $model    = $request->input('model');
            if ($provider) {
                $options['provider'] = $provider;
            }
            if ($model) {
                $options['model'] = $model;
            }

            Log::info('AI Image request', [
                'prompt'     => $prompt,
                'options'    => $options,
                'ip'         => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $manager = AIServiceManager::getInstance();
            $result  = $manager->generateImage($prompt, $options);

            Log::info('AI Image generated successfully', ['path' => $result['path'], 'url' => $result['url'] ?? '']);

            return json_success('success', $result);
        } catch (Exception $e) {
            Log::error('AI Image generation failed', [
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
                'trace'   => substr($e->getTraceAsString(), 0, 2000),
            ]);

            return json_fail($e->getMessage());
        }
    }
}
