<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Controllers;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use InnoShop\Common\Contracts\AiTool;
use InnoShop\Common\Services\AI\AgentRegistry;
use InnoShop\Common\Services\AI\LaravelToolAdapter;

class AiAgentController extends BaseController
{
    /**
     * Render chat UI for a specific agent scene.
     */
    public function index(string $scene): View
    {
        $definition = AgentRegistry::getInstance()->get($scene);
        if (! $definition) {
            abort(404, "AI Agent '{$scene}' not found");
        }

        return view('panel::ai.chat', [
            'agent'      => $definition,
            'chatApiUrl' => panel_route("{$scene}_ai.chat"),
            'toolApiUrl' => panel_route("{$scene}_ai.execute_tool"),
        ]);
    }

    /**
     * Handle chat request for a specific agent scene.
     */
    public function chat(Request $request, string $scene): JsonResponse
    {
        try {
            $definition = AgentRegistry::getInstance()->get($scene);
            if (! $definition) {
                throw new Exception("AI Agent '{$scene}' not found");
            }

            $message = $request->input('message', '');
            $history = $request->input('history', []);

            if (empty($message)) {
                throw new Exception('Empty message');
            }

            $agentClass = $definition->agentClass;

            // Build tools if the agent supports them
            $tools = [];
            if (! empty($definition->tools)) {
                foreach ($definition->tools as $toolClass) {
                    $toolInstance = new $toolClass;
                    if ($toolInstance instanceof AiTool) {
                        $tools[] = new LaravelToolAdapter($toolInstance);
                    }
                }
            }

            // Append current message to history
            $history[] = ['role' => 'user', 'content' => $message];

            // Instantiate agent using the conventional constructor pattern:
            // (array $messages, ?string $systemPrompt, array $tools)
            $agent = new $agentClass($history, null, $tools);

            $response = $agent->prompt($message);
            $result   = $response->text;

            return response()->json([
                'status'  => true,
                'message' => $result,
            ]);
        } catch (Exception $e) {
            Log::error("AI Agent chat error ({$scene}): ".$e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => __('common/base.ai_request_failed'),
            ], 500);
        }
    }

    /**
     * Execute a tool call for a specific agent scene.
     */
    public function executeTool(Request $request, string $scene): JsonResponse
    {
        $toolName = '';

        try {
            $definition = AgentRegistry::getInstance()->get($scene);
            if (! $definition) {
                throw new Exception("AI Agent '{$scene}' not found");
            }

            $toolName  = $request->input('tool_name', '');
            $arguments = $request->input('parameters', []);

            if (empty($toolName) || ! is_string($toolName)) {
                throw new Exception('Invalid tool name');
            }

            if (! is_array($arguments)) {
                throw new Exception('Parameters must be an array');
            }

            // Find the matching tool class
            $toolInstance = null;
            foreach ($definition->tools as $toolClass) {
                $instance = new $toolClass;
                if ($instance instanceof AiTool && $instance->name() === $toolName) {
                    $toolInstance = $instance;
                    break;
                }
            }

            if (! $toolInstance) {
                throw new Exception("Tool '{$toolName}' not found for agent '{$scene}'");
            }

            $result = $toolInstance->handle($arguments);

            return response()->json([
                'status' => true,
                'result' => $result,
            ]);
        } catch (Exception $e) {
            Log::error("AI Agent tool error ({$scene}/{$toolName}): ".$e->getMessage());

            return response()->json([
                'status'  => false,
                'message' => 'Tool execution failed. Please try again.',
            ], 500);
        }
    }

    /**
     * Return JSON list of all registered agents (for sidebar, menus).
     */
    public function listAgents(): JsonResponse
    {
        $agents = AgentRegistry::getInstance()->all();

        $data = [];
        foreach ($agents as $agent) {
            $data[] = [
                'scene'       => $agent->scene,
                'label'       => $agent->label,
                'icon'        => $agent->icon,
                'description' => $agent->description,
                'url'         => panel_route("{$agent->scene}_ai.index"),
            ];
        }

        return response()->json([
            'status' => true,
            'data'   => $data,
        ]);
    }
}
