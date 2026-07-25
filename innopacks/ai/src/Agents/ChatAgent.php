<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Agents;

use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Promptable;

class ChatAgent implements Agent
{
    use Promptable;

    public function __construct(
        private readonly array $messages = [],
        private readonly string $instructions = '',
    ) {}

    public function instructions(): string
    {
        return $this->instructions;
    }
}
