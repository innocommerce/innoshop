<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Http\Controllers\Panel;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Aicore\Agents\PanelChatAgent;
use InnoShop\Aicore\Services\ProviderRegistry;
use InnoShop\Panel\Controllers\BaseController;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Streaming\Events\Error;
use Laravel\Ai\Streaming\Events\StreamEnd;
use Laravel\Ai\Streaming\Events\TextDelta;
use Laravel\Ai\Streaming\Events\ToolCall;
use Laravel\Ai\Streaming\Events\ToolResult;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ChatController extends BaseController
{
    /** Maximum conversation history rounds to keep. */
    private const MAX_HISTORY_ROUNDS = 10;

    /**
     * Chat with the AI assistant via SSE streaming.
     * POST /{admin}/content-ai/chat
     */
    public function chat(Request $request): StreamedResponse
    {
        $message = trim((string) $request->get('message', ''));
        $history = (array) $request->get('history', []);

        if ($message === '') {
            throw new Exception('Empty message');
        }

        $messages = $this->buildMessages($history);

        // Honor the provider the admin selected in AI settings via the single
        // source of truth in ProviderRegistry (falls back to 'glm' if unset).
        $provider = app(ProviderRegistry::class)->getDefaultTextProvider();

        return response()->stream(function () use ($message, $messages, $provider) {
            try {
                $agent = new PanelChatAgent($messages);

                $stream = $agent->stream($message, [], $provider);

                foreach ($stream as $event) {
                    if ($event instanceof TextDelta) {
                        $this->sendSSE('delta', ['content' => $event->delta]);
                    } elseif ($event instanceof ToolCall) {
                        $this->sendSSE('tool_call', [
                            'name'      => $event->toolCall->name,
                            'arguments' => $event->toolCall->arguments,
                        ]);
                    } elseif ($event instanceof ToolResult) {
                        $this->sendSSE('tool_result', [
                            'name'   => $event->toolResult->name,
                            'result' => $event->toolResult->result ?? '',
                        ]);
                    } elseif ($event instanceof Error) {
                        $this->sendSSE('error', ['message' => $event->message]);
                    } elseif ($event instanceof StreamEnd) {
                        $this->sendSSE('done', ['finished_at' => now()->toISOString()]);
                    }
                }
            } catch (Exception $e) {
                $this->sendSSE('error', ['message' => $e->getMessage()]);
            }

            $this->sendSSE('done', ['finished_at' => now()->toISOString()]);
        }, 200, [
            'Content-Type'      => 'text/event-stream',
            'Cache-Control'     => 'no-cache',
            'Connection'        => 'keep-alive',
            'X-Accel-Buffering' => 'no',
        ]);
    }

    /**
     * Send an SSE event.
     */
    private function sendSSE(string $event, array $data): void
    {
        echo "event: {$event}\n";
        echo 'data: '.json_encode($data, JSON_UNESCAPED_UNICODE)."\n\n";
        ob_flush();
        flush();
    }

    /**
     * Build Message objects from frontend history array.
     *
     * @param  array  $history  Format: [['role' => 'user'|'assistant', 'content' => '...'], ...]
     * @return Message[]
     */
    private function buildMessages(array $history): array
    {
        $messages = [];

        foreach ($history as $entry) {
            $role    = (string) ($entry['role'] ?? '');
            $content = (string) ($entry['content'] ?? '');

            if ($role === '' || $content === '') {
                continue;
            }

            $messages[] = new Message($role, $content);
        }

        // Trim to max history rounds * 2 (user + assistant per round)
        return array_slice($messages, -self::MAX_HISTORY_ROUNDS * 2);
    }
}
