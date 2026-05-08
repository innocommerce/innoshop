<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\HtmlPurifyService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HtmlPurifyServiceTest extends TestCase
{
    #[Test]
    public function it_removes_script_tags(): void
    {
        $html   = '<p>Hello</p><script>alert("xss")</script><p>World</p>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('<script', $result);
        $this->assertStringNotContainsString('alert', $result);
        $this->assertStringContainsString('Hello', $result);
        $this->assertStringContainsString('World', $result);
    }

    #[Test]
    public function it_removes_event_handlers(): void
    {
        $html   = '<img src="test.jpg" onerror="alert(\'xss\')">';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('onerror', $result);
        $this->assertStringContainsString('src="test.jpg"', $result);
    }

    #[Test]
    public function it_removes_javascript_uri(): void
    {
        $html   = '<a href="javascript:alert(\'xss\')">Click</a>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('javascript:', $result);
    }

    #[Test]
    public function it_removes_data_uri_in_src(): void
    {
        $html   = '<img src="data:text/html,<script>alert(1)</script>">';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('data:text/html', $result);
    }

    #[Test]
    public function it_preserves_safe_html(): void
    {
        $html   = '<h2>Title</h2><p>Paragraph with <strong>bold</strong> and <em>italic</em>.</p><ul><li>Item</li></ul>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringContainsString('<h2>Title</h2>', $result);
        $this->assertStringContainsString('<strong>bold</strong>', $result);
        $this->assertStringContainsString('<em>italic</em>', $result);
        $this->assertStringContainsString('<ul><li>Item</li></ul>', $result);
    }

    #[Test]
    public function it_preserves_images_with_valid_attributes(): void
    {
        $html   = '<img src="https://example.com/photo.jpg" alt="Photo" width="300" height="200">';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringContainsString('src="https://example.com/photo.jpg"', $result);
        $this->assertStringContainsString('alt="Photo"', $result);
        $this->assertStringContainsString('width="300"', $result);
    }

    #[Test]
    public function it_preserves_tables(): void
    {
        $html   = '<table><thead><tr><th>Header</th></tr></thead><tbody><tr><td>Cell</td></tr></tbody></table>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringContainsString('<th>Header</th>', $result);
        $this->assertStringContainsString('<td>Cell</td>', $result);
    }

    #[Test]
    public function it_preserves_links_with_target(): void
    {
        $html   = '<a href="https://example.com" target="_blank" rel="noopener">Link</a>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringContainsString('href="https://example.com"', $result);
        $this->assertStringContainsString('Link', $result);
    }

    #[Test]
    public function it_removes_iframes(): void
    {
        $html   = '<iframe src="https://evil.com"></iframe><p>Safe</p>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('<iframe', $result);
        $this->assertStringContainsString('<p>Safe</p>', $result);
    }

    #[Test]
    public function it_removes_style_tags(): void
    {
        $html   = '<style>body{display:none}</style><p>Text</p>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('<style', $result);
        $this->assertStringContainsString('<p>Text</p>', $result);
    }

    #[Test]
    public function it_strips_all_html_from_non_html_fields(): void
    {
        $text   = '<b>Bold</b> and <script>evil</script>';
        $result = HtmlPurifyService::strip($text);

        $this->assertEquals('Bold and evil', $result);
    }

    #[Test]
    public function purify_translation_handles_html_and_plain_fields(): void
    {
        $input = [
            'locale'     => 'en',
            'title'      => '<b>Title</b> with <script>alert(1)</script>',
            'content'    => '<h2>Heading</h2><p>Text with <strong>bold</strong></p><script>evil()</script>',
            'summary'    => 'Plain text <b>should be stripped</b>',
            'meta_title' => '<i>Meta</i> title',
        ];

        $result = HtmlPurifyService::purifyTranslation($input);

        // content: HTML preserved but script removed
        $this->assertStringContainsString('<h2>Heading</h2>', $result['content']);
        $this->assertStringContainsString('<strong>bold</strong>', $result['content']);
        $this->assertStringNotContainsString('<script', $result['content']);

        // title: stripped (not in HTML_FIELDS)
        $this->assertStringNotContainsString('<b>', $result['title']);
        $this->assertStringNotContainsString('<script', $result['title']);
        $this->assertStringContainsString('Title', $result['title']);

        // summary: stripped
        $this->assertStringNotContainsString('<b>', $result['summary']);

        // meta_title: stripped
        $this->assertStringNotContainsString('<i>', $result['meta_title']);

        // locale: preserved
        $this->assertEquals('en', $result['locale']);
    }

    #[Test]
    public function purify_translation_handles_empty_content(): void
    {
        $input = [
            'locale'  => 'en',
            'content' => '',
        ];

        $result = HtmlPurifyService::purifyTranslation($input);

        $this->assertEquals('', $result['content']);
        $this->assertEquals('en', $result['locale']);
    }

    #[Test]
    public function it_removes_onclick_and_other_event_attributes(): void
    {
        $html   = '<div onclick="steal()" onload="evil()" onmouseover="bad()">Safe content</div>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('onclick', $result);
        $this->assertStringNotContainsString('onload', $result);
        $this->assertStringNotContainsString('onmouseover', $result);
        $this->assertStringContainsString('Safe content', $result);
    }

    #[Test]
    public function it_handles_nested_xss_vectors(): void
    {
        $html   = '<p><a href="javas<!-- -->cript:alert(1)">Click</a></p>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringNotContainsString('alert', $result);
    }

    #[Test]
    public function it_preserves_class_and_id_attributes(): void
    {
        $html   = '<div class="container" id="main">Content</div>';
        $result = HtmlPurifyService::clean($html);

        $this->assertStringContainsString('class="container"', $result);
        $this->assertStringContainsString('id="main"', $result);
    }
}
