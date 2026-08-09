<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\JsonSchema\JsonSchema as JsonSchemaFactory;
use Illuminate\JsonSchema\Types\ObjectType;
use Illuminate\JsonSchema\Types\Type;
use InnoShop\Aicore\Contracts\ToolInterface;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Schema\SchemaNormalizer;
use Laravel\Ai\Tools\Request;
use Throwable;

class LaravelAiToolAdapter implements Tool
{
    public function __construct(private readonly ToolInterface $tool) {}

    public function name(): string
    {
        return $this->tool->name();
    }

    public function description(): \Stringable|string
    {
        return $this->tool->description();
    }

    /**
     * Convert InnoShop tool inputSchema (JSON Schema array) to typed Type objects,
     * following the same pattern as McpTool.
     *
     * @return array<string, Type>
     */
    public function schema(JsonSchema $schema): array
    {
        $input = $this->tool->inputSchema();

        if (! is_array($input) || $input === []) {
            return [];
        }

        try {
            $type = JsonSchemaFactory::fromArray(SchemaNormalizer::normalize($input));
        } catch (Throwable) {
            return [];
        }

        return $type instanceof ObjectType
            ? (fn (): array => $this->properties)->call($type)
            : [];
    }

    public function handle(Request $request): \Stringable|string
    {
        $result = $this->tool->execute($request->all());

        if (is_array($result)) {
            return json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        return (string) $result;
    }
}
