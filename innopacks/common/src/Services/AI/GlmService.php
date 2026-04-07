<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

class GlmService extends OpenAIService
{
    public static function getModelInfo(): array
    {
        return [
            'name'                   => 'GLM (智谱)',
            'description'            => 'GLM large language model by Zhipu AI',
            'supports_streaming'     => true,
            'supports_function_call' => true,
            'base_url'               => 'https://open.bigmodel.cn/api/paas/v4',
            'max_tokens'             => 128000,
        ];
    }
}
