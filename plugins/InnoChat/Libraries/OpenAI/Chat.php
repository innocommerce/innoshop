<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InnoChat\Libraries\OpenAI;

use Exception;

class Chat extends Base
{
    const BASE_URL = 'https://api.siliconflow.cn/v1';

    /**
     * @var array 聊天上下文
     */
    private array $messages;

    /**
     * @param  string|null  $apiKey
     * @return static
     */
    public static function getInstance(?string $apiKey = ''): static
    {
        return new self($apiKey);
    }

    /**
     * https://platform.openai.com/docs/guides/chat/introduction
     *
     * @param  $messages
     * @param  $prompt
     * @return Chat
     */
    public function setMessages($messages, $prompt): self
    {
        $messages[]     = ['role' => 'user', 'content' => $prompt];
        $this->messages = $messages;

        return $this;
    }

    /**
     * 发送请求到 OpenAI
     *
     * @return mixed
     * @throws Exception
     */
    public function create(): mixed
    {
        $model = 'deepseek-ai/DeepSeek-V3';
        $url   = self::BASE_URL.'/chat/completions';
        $data  = [
            'messages'    => $this->messages,
            'max_tokens'  => $this->maxTokens,
            'temperature' => $this->temperature,
            'n'           => $this->number,
            'stop'        => '\n',
            'model'       => $model,
            'stream'      => true,
        ];

        return $this->request($url, $data);
    }
}
