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

            $manager = AIServiceManager::getInstance();
            $result  = $manager->generate($prompt, $purpose, $options);

            $data = [
                'message' => $result,
                'model'   => $manager->getModelForPurpose($purpose),
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
}
