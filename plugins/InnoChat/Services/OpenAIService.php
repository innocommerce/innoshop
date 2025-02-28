<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InnoChat\Services;

use Plugin\InnoChat\Libraries\OpenAI\Chat;
use Plugin\InnoChat\Models\OpenaiLog;
use Throwable;

class OpenAIService
{
    /**
     * 发起 OpenAI 请求
     *
     * @param  $data
     * @return array
     * @throws Throwable
     */
    public function requestAI($data): array
    {
        $prompt = $data['prompt'] ?? '';
        $apiKey = plugin_setting('inno_chat.api_key');

        $openAI   = Chat::getInstance($apiKey);
        $messages = $this->getChatMessages();
        $response = $openAI->setMessages($messages, $prompt)->create();

        $result['prompt'] = $prompt;

        $error = trim($response['error']['message'] ?? '');
        if ($error) {
            $result['error'] = $error;
        } else {
            $content = trim($response['choices'][0]['message']['content'] ?? '');

            $response['choices'][0]['text'] = $content;

            $result['response']       = $response;
            $newLog                   = $this->createOpenaiLog($prompt, $content);
            $result['created_format'] = $newLog->created_format;
        }

        return $result;
    }

    /**
     * @param  $question
     * @param  $answer
     * @return OpenaiLog
     * @throws Throwable
     */
    private function createOpenaiLog($question, $answer): OpenaiLog
    {
        $user         = current_admin();
        $newOpenaiLog = new OpenaiLog([
            'user_id'    => $user->id ?? 0,
            'question'   => trim($question),
            'answer'     => trim($answer),
            'request_ip' => request()->getClientIp(),
            'user_agent' => request()->userAgent(),
        ]);
        $newOpenaiLog->saveOrFail();

        return $newOpenaiLog;
    }

    /**
     * 获取聊天记录
     *
     * @param  int  $perPage
     * @return mixed
     */
    public function getOpenaiLogs(int $perPage = 10): mixed
    {
        $user = current_admin();

        return OpenaiLog::query()
            ->select(['user_id', 'question', 'answer', 'created_at'])
            ->where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate($perPage);
    }

    /**
     * https://platform.openai.com/docs/guides/chat/introduction
     *
     * messages=[
     *   {"role": "system", "content": "You are a helpful assistant."},
     *   {"role": "user", "content": "Who won the world series in 2020?"},
     *   {"role": "assistant", "content": "The Los Angeles Dodgers won the World Series in 2020."},
     *   {"role": "user", "content": "Where was it played?"}
     * ]
     *
     * @return array
     */
    public function getChatMessages(): array
    {
        $logs = OpenaiLog::query()
            ->select(['user_id', 'question', 'answer', 'created_at'])
            ->limit(5)
            ->get();

        $messages[] = [
            'role' => 'system', 'content' => 'You are a helpful assistant.',
        ];
        foreach ($logs as $log) {
            $messages[] = ['role' => 'user', 'content' => $log->question];
            $messages[] = ['role' => 'assistant', 'content' => $log->answer];
        }

        return $messages;
    }
}
