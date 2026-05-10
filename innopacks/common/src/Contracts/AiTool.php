<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Contracts;

interface AiTool
{
    /**
     * Machine-readable name for function calling schema.
     */
    public function name(): string;

    /**
     * Human-readable description for the AI model.
     */
    public function description(): string;

    /**
     * JSON Schema for parameters the AI model should provide.
     * Returns a plain array, e.g. ['type' => 'object', 'properties' => [...]]
     */
    public function parameters(): array;

    /**
     * Execute with parameters from the AI model's function call.
     * Returns a string result that gets fed back to the model.
     */
    public function handle(array $parameters): string;
}
