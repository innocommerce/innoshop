<?php

/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;

class CustomerServiceAgent implements Agent, Conversational, HasTools
{
    use Promptable;

    public function __construct(
        private readonly array $messages = [],
        private readonly ?string $systemPrompt = null,
        private readonly array $tools = [],
    ) {}

    public function instructions(): \Stringable|string
    {
        if ($this->systemPrompt) {
            return $this->systemPrompt;
        }

        return system_setting('service_bot_ai_prompt',
            'You are a helpful customer service assistant for an e-commerce store. '.
            'Be concise, friendly, and answer in the same language as the customer.'
        );
    }

    public function messages(): iterable
    {
        return array_map(
            fn (array $msg) => new Message($msg['role'], $msg['content']),
            $this->messages
        );
    }

    public function tools(): iterable
    {
        return $this->tools;
    }
}
