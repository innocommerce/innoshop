<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services\AI;

use Illuminate\Support\Facades\Log;

class AgentRegistry
{
    private static ?AgentRegistry $instance = null;

    private array $agents = [];

    private function __construct() {}

    public static function getInstance(): AgentRegistry
    {
        if (self::$instance === null) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Register an agent definition.
     * If a scene is already registered, logs a warning and ignores the duplicate.
     */
    public function register(AgentDefinition $definition): void
    {
        if (isset($this->agents[$definition->scene])) {
            Log::warning("AI Agent scene '{$definition->scene}' already registered by plugin '{$this->agents[$definition->scene]->pluginCode}', ignoring duplicate from '{$definition->pluginCode}'");

            return;
        }

        $this->agents[$definition->scene] = $definition;
    }

    /**
     * Get all registered agent definitions.
     *
     * @return AgentDefinition[]
     */
    public function all(): array
    {
        return $this->agents;
    }

    /**
     * Get a specific agent definition by scene.
     */
    public function get(string $scene): ?AgentDefinition
    {
        return $this->agents[$scene] ?? null;
    }

    /**
     * Check if a scene is registered.
     */
    public function has(string $scene): bool
    {
        return isset($this->agents[$scene]);
    }
}
