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
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ContentAIController extends BaseController
{
    /**
     * @param  Request  $request
     * @return JsonResponse
     */
    public function generate(Request $request): JsonResponse
    {
        try {
            $aiModel = system_setting('ai_model');
            if (empty($aiModel)) {
                throw new Exception('Empty AI Model');
            }

            $modelName = Str::studly($aiModel);
            $className = "Plugin\\$modelName\\Services\\{$modelName}Service";
            if (! class_exists($className)) {
                throw new Exception("Cannot found class $className");
            }

            if (! method_exists($className, 'complete')) {
                throw new Exception("Cannot found method complete for $className");
            }

            $data = [
                'message' => (new $className)->complete($request->all()),
            ];

            return read_json_success($data);
        } catch (Exception $e) {
            return json_fail($e->getMessage());
        }
    }
}
