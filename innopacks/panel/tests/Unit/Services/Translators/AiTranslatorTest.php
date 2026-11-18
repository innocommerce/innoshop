<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Unit\Services\Translators;

use Exception;
use InnoShop\Common\Support\Registry;
use InnoShop\Common\Tests\TestCase;
use InnoShop\Panel\Services\Translators\AiTranslator;
use PHPUnit\Framework\Attributes\Test;

/**
 * Test double: records every prompt and replays canned model outputs,
 * leaving prompt building, parsing and fallback logic under test.
 */
class FakeAiTranslator extends AiTranslator
{
    public array $prompts = [];

    public array $responses = [];

    public function __construct(array $responses = [])
    {
        parent::__construct();
        $this->responses = $responses;
    }

    protected function callModel(string $prompt): string
    {
        $this->prompts[] = $prompt;

        return array_shift($this->responses) ?? '';
    }
}

class AiTranslatorTest extends TestCase
{
    protected function tearDown(): void
    {
        Registry::remove('translation_type');

        parent::tearDown();
    }

    #[Test]
    public function it_returns_plain_text_for_single_translation(): void
    {
        $translator = new FakeAiTranslator(['mahsool-e khoob']);

        $result = $translator->translate('zh-cn', 'fa', '好产品');

        $this->assertSame('mahsool-e khoob', $result);
        $this->assertCount(1, $translator->prompts);
    }

    #[Test]
    public function it_skips_the_model_for_empty_text(): void
    {
        $translator = new FakeAiTranslator;

        $this->assertSame('', $translator->translate('zh-cn', 'fa', '   '));
        $this->assertSame([], $translator->prompts);
    }

    #[Test]
    public function it_states_single_mode_and_return_format_in_the_prompt(): void
    {
        $translator = new FakeAiTranslator(['mahsool-e khoob']);

        $translator->translate('zh-cn', 'fa', '好产品');

        $prompt = $translator->prompts[0];
        $this->assertStringContainsString('single translation', $prompt);
        $this->assertStringContainsString('Return ONLY the translated text', $prompt);
        $this->assertStringContainsString('Simplified Chinese to Persian (Farsi)', $prompt);
    }

    #[Test]
    public function it_keeps_html_intact_when_translation_type_is_html(): void
    {
        register('translation_type', 'html');

        $translator = new FakeAiTranslator(['<p>mahsool-e khoob</p>']);
        $translator->translate('zh-cn', 'fa', '<p>好产品</p>');

        $this->assertStringContainsString('Keep every HTML tag, attribute and placeholder exactly as-is', $translator->prompts[0]);
    }

    #[Test]
    public function it_asks_for_a_json_array_with_the_same_order_in_batch_mode(): void
    {
        $translator = new FakeAiTranslator([
            json_encode(['mahsool-e khoob', 'arzon'], JSON_UNESCAPED_UNICODE),
        ]);

        $result = $translator->batchTranslate('zh-cn', 'fa', ['好产品', '便宜']);

        $this->assertSame(['mahsool-e khoob', 'arzon'], $result);
        $prompt = $translator->prompts[0];
        $this->assertStringContainsString('batch translation', $prompt);
        $this->assertStringContainsString('valid JSON array', $prompt);
        $this->assertStringContainsString('SAME length and the SAME order', $prompt);
        $this->assertStringContainsString('["好产品","便宜"]', $prompt);
    }

    #[Test]
    public function it_tolerates_markdown_fence_around_the_json_array(): void
    {
        $translator = new FakeAiTranslator([
            "```json\n[\"mahsool-e khoob\", \"arzon\"]\n```",
        ]);

        $result = $translator->batchTranslate('zh-cn', 'fa', ['好产品', '便宜']);

        $this->assertSame(['mahsool-e khoob', 'arzon'], $result);
    }

    #[Test]
    public function it_falls_back_to_single_translations_when_the_batch_result_is_misaligned(): void
    {
        $translator = new FakeAiTranslator([
            json_encode(['mahsool-e khoob'], JSON_UNESCAPED_UNICODE),
            'mahsool-e khoob',
            'arzon',
        ]);

        $result = $translator->batchTranslate('zh-cn', 'fa', ['好产品', '便宜']);

        $this->assertSame(['mahsool-e khoob', 'arzon'], $result);
        $this->assertCount(3, $translator->prompts);
        $this->assertStringContainsString('single translation', $translator->prompts[1]);
    }

    #[Test]
    public function it_falls_back_to_single_translations_when_the_model_returns_no_json(): void
    {
        $translator = new FakeAiTranslator([
            'I cannot translate this.',
            'mahsool-e khoob',
            'arzon',
        ]);

        $result = $translator->batchTranslate('zh-cn', 'fa', ['好产品', '便宜']);

        $this->assertSame(['mahsool-e khoob', 'arzon'], $result);
        $this->assertCount(3, $translator->prompts);
    }

    #[Test]
    public function it_splits_large_batches_into_multiple_requests(): void
    {
        $texts = array_map(fn ($i) => (string) $i, range(1, 25));

        $translator = new FakeAiTranslator([
            json_encode(array_slice($texts, 0, 20)),
            json_encode(array_slice($texts, 20)),
        ]);

        $result = $translator->batchTranslate('zh-cn', 'fa', $texts);

        $this->assertSame($texts, $result);
        $this->assertCount(2, $translator->prompts);
    }

    #[Test]
    public function it_throws_when_the_model_returns_nothing(): void
    {
        $translator = new FakeAiTranslator(['']);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('empty result');

        $translator->translate('zh-cn', 'fa', '好产品');
    }

    #[Test]
    public function it_maps_locale_codes_to_language_names(): void
    {
        $translator = new AiTranslator;

        $this->assertSame('Persian (Farsi)', $translator->mapCode('fa'));
        $this->assertSame('Simplified Chinese', $translator->mapCode('zh-cn'));
        $this->assertSame('xx', $translator->mapCode('xx'));
    }
}
