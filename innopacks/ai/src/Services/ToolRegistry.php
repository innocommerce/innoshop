<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\AI\Services;

use InnoShop\AI\Contracts\ToolInterface;
use LogicException;

/**
 * Registry for AI tools. Plugins register via the `ai.tools` hook filter,
 * which receives this registry instance on first access (deferred so that
 * late-booted plugin providers don't miss it).
 */
class ToolRegistry
{
    /** @var array<string, ToolInterface> */
    private array $tools = [];

    private bool $booted = false;

    public function register(ToolInterface $tool): self
    {
        $name = $tool->name();
        if (isset($this->tools[$name])) {
            throw new LogicException("AI tool [{$name}] is already registered.");
        }

        $this->tools[$name] = $tool;

        return $this;
    }

    public function get(string $name): ?ToolInterface
    {
        $this->ensureBooted();

        return $this->tools[$name] ?? null;
    }

    public function has(string $name): bool
    {
        $this->ensureBooted();

        return isset($this->tools[$name]);
    }

    /**
     * @return array<string, ToolInterface>
     */
    public function all(): array
    {
        $this->ensureBooted();

        return $this->tools;
    }

    /**
     * Tools the given permission checker grants access to.
     * Tools without a required permission are always included.
     *
     * @param  callable(string $permission): bool  $can
     * @return array<string, ToolInterface>
     */
    public function permitted(callable $can): array
    {
        $this->ensureBooted();

        return array_filter(
            $this->tools,
            fn (ToolInterface $tool) => ($permission = $tool->requiredPermission()) === null || $can($permission)
        );
    }

    /**
     * Serializable tool descriptors (name/description/inputSchema) for MCP
     * tools/list or LLM function calling, optionally permission-filtered.
     *
     * @param  (callable(string $permission): bool)|null  $can
     */
    public function schemas(?callable $can = null): array
    {
        $tools = $can ? $this->permitted($can) : $this->all();

        return array_values(array_map(fn (ToolInterface $tool) => [
            'name'        => $tool->name(),
            'description' => $tool->description(),
            'inputSchema' => $tool->inputSchema(),
        ], $tools));
    }

    /**
     * Fire the registration hook once, on first read. Deferred because plugin
     * providers boot after AIServiceProvider and would miss an early hook.
     */
    private function ensureBooted(): void
    {
        if ($this->booted) {
            return;
        }
        $this->booted = true;

        if (! function_exists('fire_hook_filter')) {
            return;
        }

        try {
            fire_hook_filter('ai.tools', $this);
        } catch (\Throwable) {
            // Hook system unavailable outside Laravel bootstrap (e.g. plain unit tests).
        }
    }
}
