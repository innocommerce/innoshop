<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\MCP\Tools;

use InnoShop\AI\Contracts\ToolInterface;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Tool;
use stdClass;
use Throwable;

/**
 * Adapts an innopacks/ai ToolInterface into a laravel/mcp Tool so every
 * registered tool is automatically exposed over MCP.
 */
class RegistryToolAdapter extends Tool
{
    public function __construct(private readonly ToolInterface $tool) {}

    public function name(): string
    {
        return $this->tool->name();
    }

    public function description(): string
    {
        return $this->tool->description();
    }

    public function toArray(): array
    {
        return [
            'name'        => $this->tool->name(),
            'description' => $this->tool->description(),
            'inputSchema' => $this->tool->inputSchema() ?: ['type' => 'object', 'properties' => new stdClass],
            'annotations' => new stdClass,
        ];
    }

    public function handle(Request $request): Response
    {
        $permission = $this->tool->requiredPermission();
        if ($permission !== null) {
            $user = $request->user();
            if (! $user || ! $user->can($permission)) {
                return Response::error("Permission denied: [{$permission}] is required.");
            }
        }

        try {
            $result = $this->tool->execute($request->all());

            return is_string($result) ? Response::text($result) : Response::json($result);
        } catch (Throwable $e) {
            return Response::error($e->getMessage());
        }
    }
}
