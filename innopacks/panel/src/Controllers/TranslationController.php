<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use InnoShop\Panel\Requests\TranslateRequest;
use InnoShop\Panel\Services\TranslatorService;

class TranslationController extends Controller
{
    /**
     * @param  TranslateRequest  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function translate(TranslateRequest $request): JsonResponse
    {
        $source = $request->get('source');
        $target = $request->get('target');
        $text   = $request->get('text');

        $response = TranslatorService::getInstance()->translate($source, $target, $text);

        return create_json_success($response);
    }
}
