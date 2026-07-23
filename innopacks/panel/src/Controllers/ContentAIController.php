<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use InnoShop\Common\Agents\ContentAgent;
use InnoShop\Common\Models\Article;
use InnoShop\Common\Models\Product;
use InnoShop\Common\Services\AI\AIServiceManager;

class ContentAIController extends BaseController
{
    /**
     * @param  Request  $request
     * @return mixed
     */
    public function generate(Request $request): mixed
    {
        try {
            // Log all request parameters
            $allParams = $request->all();
            Log::info('AI Generate Request: '.json_encode($allParams));

            $purpose = $request->get('purpose', 'general');
            $prompt  = $request->get('prompt', '');
            $options = $request->get('options', []);

            // Compatible with frontend form format: get prompt from value field
            if (empty($prompt) && $request->has('value')) {
                $prompt = $request->get('value');
            }

            // Add column and lang parameters from frontend to options
            if ($request->has('column')) {
                $options['column'] = $request->get('column');
            }
            if ($request->has('lang')) {
                $options['lang'] = $request->get('lang');
            }

            if (empty($prompt)) {
                throw new Exception('Empty prompt');
            }

            $entityType = (string) ($options['entity_type'] ?? $request->get('entity_type', ''));
            $entityId   = (int) ($options['entity_id'] ?? $request->get('entity_id', 0));
            $context    = $this->buildEntityContext($entityType, $entityId);

            $agent    = new ContentAgent($options['column'] ?? '', $options['lang'] ?? '', $context);
            $response = $agent->prompt($prompt);
            $result   = ContentAgent::cleanContent($response->text);

            $data = [
                'message' => $result,
                'model'   => system_setting('ai_model', 'openai'),
            ];

            Log::info('AI Generate Success: '.json_encode($data));

            return read_json_success($data);
        } catch (Exception $e) {
            Log::error('AI Generate Error: '.$e->getMessage().' in '.$e->getFile().':'.$e->getLine());
            Log::error('AI Generate Stack: '.$e->getTraceAsString());

            return json_fail($e->getMessage());
        }
    }

    /**
     * Get available AI model list
     *
     * @return mixed
     */
    public function getModels(): mixed
    {
        try {
            $manager = AIServiceManager::getInstance();
            $models  = $manager->getModelsForSelect();

            return read_json_success([
                'models' => $models,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * Batch generate content for multiple locales in one request.
     *
     * Request payload:
     *   - column:      string  ContentAgent column key (e.g. product_summary)
     *   - prompt:      string  User-supplied prompt text (already finalized by FE)
     *   - locales:     array   List of locale codes to generate for
     *   - entity_type: string  Optional 'product' or 'article' for context enrichment
     *   - entity_id:   int     Optional entity id
     *
     * @param  Request  $request
     * @return mixed
     */
    public function generateBatch(Request $request): mixed
    {
        try {
            $prompt     = trim((string) $request->get('prompt', ''));
            $column     = (string) $request->get('column', '');
            $locales    = (array) $request->get('locales', []);
            $entityType = (string) $request->get('entity_type', '');
            $entityId   = (int) $request->get('entity_id', 0);

            Log::info('AI Generate Batch Request: '.json_encode([
                'column'      => $column,
                'prompt'      => mb_substr($prompt, 0, 100),
                'locales'     => $locales,
                'entity_type' => $entityType,
                'entity_id'   => $entityId,
            ]));

            if ($prompt === '' || $column === '' || empty($locales)) {
                throw new Exception('Missing required parameters (prompt, column, locales)');
            }

            $context = $this->buildEntityContext($entityType, $entityId);

            $results = [];
            foreach ($locales as $locale) {
                $locale = (string) $locale;
                if ($locale === '') {
                    continue;
                }
                try {
                    $agent    = new ContentAgent($column, $locale, $context);
                    $response = $agent->prompt($prompt);
                    $text     = trim((string) ($response->text ?? ''));
                    if (in_array($column, ['product_content', 'article_content', 'category_content', 'brand_content', 'page_content'], true)) {
                        $text = ContentAgent::cleanContent($text);
                    }
                    $results[$locale] = ['text' => $text, 'error' => null];
                } catch (Exception $e) {
                    Log::warning("AI batch generate failed for locale={$locale} column={$column}: ".$e->getMessage());
                    $results[$locale] = ['text' => '', 'error' => $e->getMessage()];
                }
            }

            return read_json_success(['results' => $results]);
        } catch (Exception $e) {
            Log::error('AI Generate Batch Error: '.$e->getMessage());

            return json_fail($e->getMessage());
        }
    }

    /**
     * Test model configuration
     *
     * @param  Request  $request
     * @return mixed
     */
    public function testModel(Request $request): mixed
    {
        try {
            $model  = $request->get('model');
            $config = $request->get('config', []);

            if (empty($model)) {
                throw new Exception('Empty model name');
            }

            $manager = AIServiceManager::getInstance();
            $isValid = $manager->validateModelConfig($model, $config);

            return read_json_success([
                'valid' => $isValid,
            ]);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }

    /**
     * List available models from a provider (calls provider's /models endpoint).
     *
     * @param  Request  $request
     * @return mixed
     */
    public function listModels(Request $request): mixed
    {
        try {
            $providerCode = (string) $request->get('provider_code', '');
            $apiKey       = (string) $request->get('api_key', '');
            $baseUrl      = (string) $request->get('base_url', '');

            if ($providerCode === '') {
                throw new Exception('Missing provider_code');
            }

            $models = AIServiceManager::getInstance()->fetchAvailableModels($providerCode, $apiKey, $baseUrl);

            Log::info('AI listModels success', ['count' => count($models), 'sample' => array_slice($models, 0, 3)]);

            return read_json_success(['models' => $models]);
        } catch (Exception $e) {
            Log::error('AI listModels failed', ['error' => $e->getMessage()]);

            return json_fail($e->getMessage());
        }
    }

    /**
     * Build context array for an entity (product/article) to enrich AI prompts.
     * Returns empty array on any failure — caller proceeds without context.
     *
     * @param  string  $type
     * @param  int  $id
     * @return array
     */
    private function buildEntityContext(string $type, int $id): array
    {
        if (empty($type) || $id <= 0) {
            return [];
        }

        try {
            if ($type === 'product') {
                $product = Product::with(['brand', 'categories'])->find($id);
                if (! $product) {
                    return [];
                }

                $title    = trim((string) ($product->title ?? ''));
                $brand    = trim((string) ($product->brand?->name ?? ''));
                $category = $product->categories
                    ->map(fn ($c) => trim((string) ($c->name ?? '')))
                    ->filter()
                    ->implode(', ');

                return array_filter([
                    'title'    => $title,
                    'brand'    => $brand,
                    'category' => $category,
                    'model'    => trim((string) ($product->model ?? '')),
                ]);
            }

            if ($type === 'article') {
                $article = Article::query()->find($id);
                if (! $article) {
                    return [];
                }

                return array_filter([
                    'title' => trim((string) ($article->title ?? '')),
                ]);
            }
        } catch (Exception $e) {
            Log::warning('ContentAI buildEntityContext failed: '.$e->getMessage());
        }

        return [];
    }
}
