<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use InnoShop\Aicore\Http\Controllers\PanelApi\AIImageController;
use InnoShop\Aicore\Images\ImageDriverManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MinimaxImageGenerationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        Http::preventStrayRequests();
        app('eventy')->addFilter('ai.image_generate_driver', fn () => ImageDriverManager::class);
        config(['ai.providers.minimax.key' => 'fixture']);
    }

    private function generate(string $model, array $options = []): array
    {
        $request = Request::create('/ai/generate_image', 'POST', $options + [
            'prompt'   => 'A ceramic vase',
            'provider' => 'minimax',
            'model'    => $model,
        ]);

        return (new AIImageController)->generate($request)->getData(true);
    }

    public static function regions(): array
    {
        return [
            ['https://api.minimax.io/v1', 'MiniMax-H3-Image', '3:2', 'aspect_ratio'],
            ['https://api.minimax.cn/v1', 'MiniMax-H3-Image-Highspeed', '1536x1024', 'size'],
        ];
    }

    #[DataProvider('regions')]
    public function test_h3_saves_successful_images_and_reports_item_errors(string $base, string $model, string $size, string $sizeField): void
    {
        config(['ai.providers.minimax.url' => $base]);
        Http::fake(function (ClientRequest $request, array $options) use ($base, $model, $size, $sizeField) {
            $this->assertSame('POST', $request->method());
            $this->assertSame($base.'/images/generations', $request->url());
            $this->assertTrue($request->hasHeader('Authorization', 'Bearer fixture'));
            $this->assertEquals(600, $options['timeout']);
            $this->assertEquals([
                'model'           => $model, 'prompt' => 'A ceramic vase', 'n' => 4,
                'response_format' => 'b64_json', 'output_format' => 'jpeg', $sizeField => $size,
            ], $request->data());

            return Http::response(['data' => [
                ['error' => ['code' => '1000', 'message' => 'One image failed']],
                ['b64_json' => base64_encode('first image')],
                ['b64_json' => base64_encode('second image')],
                ['b64_json' => base64_encode('third image')],
            ]]);
        });

        $body = $this->generate($model, ['size' => $size, 'count' => 4]);
        $this->assertTrue($body['success'], $body['message']);
        $result = $body['data'];
        $this->assertSame('first image', Storage::disk('media')->get('ai-images/'.$result['name']));
        $this->assertCount(3, Storage::disk('media')->files('ai-images'));
        $this->assertStringEndsWith('.jpg', $result['name']);
        $this->assertSame($result['url'], $result['origin_url']);
        $this->assertStringContainsString('One image failed', $result['notice']);
        Http::assertSentCount(1);
    }

    public static function errors(): array
    {
        return [
            [429, ['error' => ['message' => 'Rate limited']], 'Rate limited'],
            [200, ['data' => [['error' => ['code' => '1001', 'message' => 'Generation timed out']]]], 'Generation timed out'],
            [200, ['data' => [['b64_json' => '!!']]], 'no image data'],
        ];
    }

    #[DataProvider('errors')]
    public function test_h3_reports_generation_errors_without_retrying(int $status, array $response, string $message): void
    {
        config(['ai.providers.minimax.url' => 'https://api.minimax.io/v1']);
        Http::fake(['*/images/generations' => Http::response($response, $status)]);
        $body = $this->generate('MiniMax-H3-Image');
        $this->assertFalse($body['success']);
        $this->assertStringContainsString($message, $body['message']);
        Http::assertSentCount(1);
    }

    public function test_h3_rejects_reference_images_before_any_request(): void
    {
        config(['ai.providers.minimax.url' => 'https://api.minimax.io/v1']);
        Storage::disk('media')->put('ref.png', 'fake reference');
        $body = $this->generate('MiniMax-H3-Image', ['reference_image' => 'ref.png']);
        $this->assertFalse($body['success']);
        $this->assertStringContainsString('accepts text prompts only', $body['message']);
        Http::assertNothingSent();
    }

    public function test_h3_requires_a_regional_base_url(): void
    {
        config(['ai.providers.minimax.url' => 'https://api.minimax.chat/v1']);
        $body = $this->generate('MiniMax-H3-Image');
        $this->assertFalse($body['success']);
        $this->assertStringContainsString('https://api.minimax.io/v1', $body['message']);
        Http::assertNothingSent();
    }

    public function test_legacy_model_keeps_its_endpoint_and_image_url(): void
    {
        config(['ai.providers.minimax.url' => 'https://api.minimax.chat/v1']);
        $url = 'https://images.example.test/vase.png';
        Http::fake([
            '*/image_generation' => Http::response(['data' => ['image_urls' => [$url]]]),
            $url                 => Http::response('legacy image'),
        ]);
        $body = $this->generate('image-01');
        $this->assertTrue($body['success'], $body['message']);
        $this->assertSame($url, $body['data']['origin_url']);
        $this->assertSame('legacy image', Storage::disk('media')->get('ai-images/'.$body['data']['name']));
    }
}
