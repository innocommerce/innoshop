<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Agents;

use InnoShop\Common\Agents\ContentAgent;
use InnoShop\Common\Tests\TestCase;

class ContentAgentTest extends TestCase
{
    public function test_instructions_returns_base_prompt_without_column(): void
    {
        $agent = new ContentAgent;
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString('expert e-commerce content writer', $text);
        $this->assertStringNotContainsString('Respond ONLY in', $text);
        $this->assertStringNotContainsString('Entity context', $text);
    }

    public function test_instructions_appends_locale_when_provided(): void
    {
        $agent = new ContentAgent('', 'zh-cn');
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString("Respond ONLY in the language corresponding to locale 'zh-cn'", $text);
    }

    public function test_instructions_accepts_known_column_without_error(): void
    {
        $agent = new ContentAgent('product_summary', 'en');
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString('expert e-commerce content writer', $text);
        $this->assertStringContainsString("Respond ONLY in the language corresponding to locale 'en'", $text);
    }

    public function test_instructions_handles_unknown_column_gracefully(): void
    {
        $agent = new ContentAgent('unknown_column_xyz', 'en');
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString('expert e-commerce content writer', $text);
    }

    public function test_instructions_appends_context_when_provided(): void
    {
        $agent = new ContentAgent('product_summary', 'en', [
            'title'    => 'Test Product',
            'brand'    => 'Acme',
            'category' => 'Electronics',
        ]);
        $text = (string) $agent->instructions();

        $this->assertStringContainsString('Entity context', $text);
        $this->assertStringContainsString('Title: Test Product', $text);
        $this->assertStringContainsString('Brand: Acme', $text);
        $this->assertStringContainsString('Category: Electronics', $text);
    }

    public function test_instructions_skips_empty_context_values(): void
    {
        $agent = new ContentAgent('product_summary', 'en', [
            'title'    => 'Test Product',
            'brand'    => '',
            'category' => '   ',
            'model'    => null,
        ]);
        $text = (string) $agent->instructions();

        $this->assertStringContainsString('Title: Test Product', $text);
        $this->assertStringNotContainsString('Brand:', $text);
        $this->assertStringNotContainsString('Category:', $text);
        $this->assertStringNotContainsString('Model:', $text);
    }

    public function test_instructions_handles_array_context_values(): void
    {
        $agent = new ContentAgent('product_summary', 'en', [
            'tags' => ['summer', 'sale', ''],
        ]);
        $text = (string) $agent->instructions();

        $this->assertStringContainsString('Tags: summer, sale', $text);
    }

    public function test_instructions_skips_context_block_when_all_empty(): void
    {
        $agent = new ContentAgent('product_summary', 'en', [
            'title' => '',
            'brand' => null,
        ]);
        $text = (string) $agent->instructions();

        $this->assertStringNotContainsString('Entity context', $text);
    }

    public function test_instructions_handles_new_product_content_column(): void
    {
        $agent = new ContentAgent('product_content', 'en');
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString('expert e-commerce content writer', $text);
        $this->assertStringContainsString('HTML body fragment', $text);
        $this->assertStringContainsString('Do NOT output', $text);
    }

    public function test_instructions_handles_new_article_content_column(): void
    {
        $agent = new ContentAgent('article_content', 'en');
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString('expert e-commerce content writer', $text);
        $this->assertStringContainsString('HTML body fragment', $text);
        $this->assertStringContainsString('Do NOT output', $text);
    }

    public function test_instructions_handles_new_category_content_column(): void
    {
        $agent = new ContentAgent('category_content', 'en');
        $text  = (string) $agent->instructions();

        $this->assertStringContainsString('expert e-commerce content writer', $text);
        $this->assertStringContainsString('category description content', $text);
        $this->assertStringContainsString('Do NOT output', $text);
    }

    public function test_clean_content_extracts_body_from_full_html_document(): void
    {
        $raw = "以下是您所需的HTML正文内容：\n\n```html\n<!DOCTYPE html\n<html lang='zh-cn'>\n<head><title>Test</title></head>\n<body>\n<h2>Title</h2>\n<p>Paragraph</p>\n</body>\n</html>\n```";

        $cleaned = ContentAgent::cleanContent($raw);

        $this->assertStringContainsString('<h2>Title</h2>', $cleaned);
        $this->assertStringContainsString('<p>Paragraph</p>', $cleaned);
        $this->assertStringNotContainsString('<!DOCTYPE>', $cleaned);
        $this->assertStringNotContainsString('<html>', $cleaned);
        $this->assertStringNotContainsString('<head>', $cleaned);
        $this->assertStringNotContainsString('<body>', $cleaned);
        $this->assertStringNotContainsString('以下是您所需', $cleaned);
        $this->assertStringNotContainsString('```', $cleaned);
    }

    public function test_clean_content_returns_plain_html_fragment_unchanged(): void
    {
        $raw = "<h2>Title</h2>\n<p>Paragraph</p>";

        $this->assertEquals($raw, ContentAgent::cleanContent($raw));
    }

    public function test_clean_content_returns_empty_string_for_empty_input(): void
    {
        $this->assertEquals('', ContentAgent::cleanContent(''));
        $this->assertEquals('', ContentAgent::cleanContent('   '));
    }
}
