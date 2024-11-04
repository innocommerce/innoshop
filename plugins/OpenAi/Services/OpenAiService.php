<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\OpenAi\Services;

use Exception;
use Illuminate\Http\Client\ConnectionException;
use Plugin\OpenAi\Libraries\OpenAI;

class OpenAiService
{
    /**
     * @param  $requestData
     * @return mixed
     * @throws ConnectionException
     * @throws Exception
     */
    public function complete($requestData): mixed
    {
        $result = OpenAI::getInstance()->completions($requestData);

        if (isset($result['error'])) {
            throw new Exception($result['error']['message']);
        }

        return $result['choices'][0]['message']['content'];
    }
}
