<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

use InnoShop\Common\Contracts\AiTool;
use Laravel\Ai\Contracts\Tool as LaravelToolContract;
use Laravel\Ai\Tools\Request;

class LaravelToolAdapter implements LaravelToolContract
{
    public function __construct(private readonly AiTool $tool) {}

    public function description(): string
    {
        return $this->tool->description();
    }

    public function handle(Request $request): string
    {
        return $this->tool->handle($request->all());
    }

    public function schema($schema): array
    {
        return $this->tool->parameters();
    }

    /**
     * Get the underlying AiTool instance.
     */
    public function getTool(): AiTool
    {
        return $this->tool;
    }
}
