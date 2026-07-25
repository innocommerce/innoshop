<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Tools;

use InnoShop\AI\Contracts\ToolInterface;

abstract class BaseTool implements ToolInterface
{
    public function inputSchema(): array
    {
        return [
            'type'       => 'object',
            'properties' => new \stdClass,
        ];
    }

    public function requiredPermission(): ?string
    {
        return null;
    }
}
