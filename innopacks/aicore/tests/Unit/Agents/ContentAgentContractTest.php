<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Unit\Agents;

use InnoShop\Aicore\Agents\ContentAgent;
use Laravel\Ai\Contracts\Agent;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

class ContentAgentContractTest extends TestCase
{
    public function test_prompt_signature_matches_the_agent_contract(): void
    {
        // Instantiating fatals at class load if the override narrows the
        // contract's $prompt type (laravel/ai 1.x widened it to AgentInput).
        $agent = new ContentAgent();
        $this->assertInstanceOf(Agent::class, $agent);

        $contract = (string) (new ReflectionMethod(Agent::class, 'prompt'))->getParameters()[0]->getType();
        $override = (string) (new ReflectionMethod(ContentAgent::class, 'prompt'))->getParameters()[0]->getType();

        $this->assertSame($contract, $override);
    }
}
