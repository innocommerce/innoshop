<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

class MinimaxService extends OpenAIService
{
    public static function getModelInfo(): array
    {
        return [
            'name'                   => 'MiniMax',
            'description'            => 'MiniMax large language model',
            'supports_streaming'     => true,
            'supports_function_call' => true,
            'base_url'               => 'https://api.minimax.chat/v1',
            'max_tokens'             => 128000,
        ];
    }
}
