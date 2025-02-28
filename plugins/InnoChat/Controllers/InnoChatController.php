<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\InnoChat\Controllers;

use Exception;
use Illuminate\Http\Request;
use InnoShop\Panel\Controllers\BaseController;
use Plugin\InnoChat\Services\OpenAIService;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class InnoChatController extends BaseController
{
    /**
     * OpenAI home page.
     *
     * @return mixed
     * @throws Exception
     */
    public function index(): mixed
    {
        $plugin = plugin('inno_chat');

        $error  = '';
        $apiKey = plugin_setting('inno_chat.api_key');
        if (empty($apiKey)) {
            $error = trans('InnoChat::common.empty_api_key');
        }
        $baseUrl = config('app.url').'/panel/inno_chat';

        $data = [
            'name'        => $plugin->getLocaleName(),
            'description' => $plugin->getLocaleDescription(),
            'base'        => $baseUrl,
            'error'       => $error,
        ];

        return view('InnoChat::panel.deepseek', $data);
    }

    /**
     * Send chat completions with OpenAI API
     *
     * @param  Request  $request
     * @return array|mixed
     * @throws Throwable
     */
    public function completions(Request $request): mixed
    {
        $question = $request->query('question');

        return response()->stream(function () use ($question) {
            $apiKey = plugin_setting('inno_chat.api_key');

            $client = \OpenAI::factory()
                ->withApiKey($apiKey)
                ->withBaseUri('https://api.siliconflow.cn/v1')
                ->withHttpClient($httpClient = new \GuzzleHttp\Client([]))
                ->withStreamHandler(fn (RequestInterface $request): ResponseInterface => $httpClient->send($request, [
                    'stream' => true,
                ]))
                ->make();

            $stream = $client->chat()->createStreamed([
                'model'       => 'deepseek-ai/DeepSeek-V3',
                'temperature' => 0.8,
                'messages'    => [
                    [
                        'role'    => 'user',
                        'content' => $question,
                    ],
                ],
                'max_tokens' => 1024,
            ]);

            foreach ($stream as $response) {
                $text = $response->choices[0]->delta->content;
                if (connection_aborted()) {
                    break;
                }

                echo "event: update\n";
                echo 'data: '.$text;
                echo "\n\n";
                ob_flush();
                flush();
            }

            echo "event: update\n";
            echo 'data: <END_STREAMING_SSE>';
            echo "\n\n";
            ob_flush();
            flush();
        }, 200, [
            'Cache-Control'     => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type'      => 'text/event-stream',
        ]);
    }

    /**
     * Get histories
     *
     * @param  Request  $request
     * @return array|mixed
     */
    public function histories(Request $request): mixed
    {
        try {
            $perPage = $request->get('per_page', 10);
            $result  = (new OpenAIService)->getOpenaiLogs($perPage);
        } catch (Exception $e) {
            $result = [
                'error' => $e->getMessage(),
            ];
        }

        return $result;
    }
}
