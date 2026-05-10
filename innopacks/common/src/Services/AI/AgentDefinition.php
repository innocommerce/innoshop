<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

class AgentDefinition
{
    public function __construct(
        public readonly string $scene,
        public readonly string $label,
        public readonly string $agentClass,
        public readonly string $icon = 'bi-robot',
        public readonly string $description = '',
        public readonly array $tools = [],
        public readonly array $options = [],
        public readonly string $pluginCode = '',
    ) {}
}
