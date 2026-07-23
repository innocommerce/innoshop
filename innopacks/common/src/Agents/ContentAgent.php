<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Agents;

use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Enums\Lab;
use Laravel\Ai\Promptable;
use Laravel\Ai\Responses\AgentResponse;

class ContentAgent implements Agent
{
    use Promptable {
        prompt as promptViaTrait;
    }

    public function __construct(
        private readonly string $column = '',
        private readonly string $locale = '',
        private readonly array $context = [],
    ) {}

    /**
     * Prepend a strong, locale-specific language instruction to the user prompt
     * before delegating to the trait implementation.
     */
    public function prompt(
        Decisions|string $prompt,
        array $attachments = [],
        Lab|array|string|null $provider = null,
        ?string $model = null,
        ?int $timeout = null,
    ): AgentResponse {
        if ($this->locale !== '' && is_string($prompt)) {
            $prompt = $this->localizePrompt($prompt);
        }

        return $this->promptViaTrait($prompt, $attachments, $provider, $model, $timeout);
    }

    /**
     * Wrap the user prompt with an explicit language requirement.
     */
    private function localizePrompt(string $prompt): string
    {
        $instruction = match ($this->locale) {
            'en', 'en-us', 'en-gb' => 'You MUST respond entirely in English. Do not use Chinese or any other language.',
            'zh-cn', 'zh-hans'     => '你必须完全使用简体中文回答。不要使用英文或其他语言。',
            'zh-tw', 'zh-hant'     => '你必須完全使用繁體中文回答。不要使用英文或其他語言。',
            'ja'                   => '日本語のみで回答してください。他の言語は使用しないでください。',
            'ko'                   => '한국어로만 답변하세요. 다른 언어를 사용하지 마세요.',
            default                => "You MUST respond entirely in the language for locale '{$this->locale}'. Do not mix languages.",
        };

        return "[LANGUAGE REQUIREMENT]\n{$instruction}\n\n[REQUEST]\n{$prompt}";
    }

    public function instructions(): \Stringable|string
    {
        $basePrompt = 'You are an expert e-commerce content writer. Generate high-quality, SEO-optimized content.';

        if ($this->column) {
            $mappings = [
                'product_summary'       => 'ai_prompt_product_summary',
                'product_selling_point' => 'ai_prompt_product_selling_point',
                'product_slug'          => 'ai_prompt_product_slug',
                'product_title'         => 'ai_prompt_product_seo_title',
                'product_description'   => 'ai_prompt_product_seo_description',
                'product_keywords'      => 'ai_prompt_product_seo_keywords',
                'product_content'       => 'ai_prompt_product_content',
                'article_summary'       => 'ai_prompt_article_summary',
                'article_slug'          => 'ai_prompt_article_slug',
                'article_title'         => 'ai_prompt_article_seo_title',
                'article_description'   => 'ai_prompt_article_seo_description',
                'article_keywords'      => 'ai_prompt_article_seo_keywords',
                'article_content'       => 'ai_prompt_article_content',
                'category_content'      => 'ai_prompt_category_content',
                'brand_content'         => 'ai_prompt_brand_content',
                'page_content'          => 'ai_prompt_page_content',
            ];

            $systemPrompt = system_setting($mappings[$this->column] ?? '') ?: '';
            if (! $systemPrompt && in_array($this->column, ['product_content', 'article_content', 'category_content', 'brand_content', 'page_content'], true)) {
                $systemPrompt = $this->defaultContentPrompt($this->column);
            }
            if ($systemPrompt) {
                $basePrompt .= "\n\n".$systemPrompt;
            }
        }

        if ($this->locale) {
            $basePrompt .= "\n\nIMPORTANT: Respond ONLY in the language corresponding to locale '{$this->locale}'. Do not mix languages or output in other languages.";
        }

        if (! empty($this->context)) {
            $lines = [];
            foreach ($this->context as $key => $value) {
                if (is_array($value)) {
                    $value = implode(', ', array_filter(array_map('trim', $value)));
                }
                $value = is_string($value) ? trim((string) $value) : '';
                if ($value !== '') {
                    $lines[] = '- '.ucfirst(str_replace('_', ' ', (string) $key)).': '.$value;
                }
            }
            if (! empty($lines)) {
                $basePrompt .= "\n\nEntity context:\n".implode("\n", $lines);
            }
        }

        return $basePrompt;
    }

    /**
     * Default system prompt for rich-text content generation when no custom prompt is configured.
     */
    private function defaultContentPrompt(string $column): string
    {
        $type = match (true) {
            str_starts_with($column, 'product_')  => 'product',
            str_starts_with($column, 'category_') => 'category',
            str_starts_with($column, 'brand_')    => 'brand',
            str_starts_with($column, 'page_')     => 'page',
            default                               => 'article',
        };

        return match ($type) {
            'product' => "Generate product description content as clean HTML suitable for a WYSIWYG editor.\n"
                ."Rules:\n"
                ."- Output ONLY the HTML body fragment.\n"
                ."- Do NOT output '<!DOCTYPE>', '<html>', '<head>', '<body>', '<title>', '<meta>', or any document-level tags.\n"
                ."- Do NOT wrap the response in Markdown code blocks (```html ... ```).\n"
                ."- Do NOT add explanations, introductions, or summaries before or after the HTML.\n"
                ."- Use only semantic tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a>, <blockquote>, <img>.\n"
                .'- Keep paragraphs concise, use bullet lists for features/benefits, and include a clear value proposition.',
            'category' => "Generate category description content as clean HTML suitable for a WYSIWYG editor.\n"
                ."Rules:\n"
                ."- Output ONLY the HTML body fragment.\n"
                ."- Do NOT output '<!DOCTYPE>', '<html>', '<head>', '<body>', '<title>', '<meta>', or any document-level tags.\n"
                ."- Do NOT wrap the response in Markdown code blocks (```html ... ```).\n"
                ."- Do NOT add explanations, introductions, or summaries before or after the HTML.\n"
                ."- Use only semantic tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a>, <blockquote>, <img>.\n"
                .'- Introduce the category, highlight what shoppers can find, and use bullet lists for key highlights.',
            'brand' => "Generate brand description content as clean HTML suitable for a WYSIWYG editor.\n"
                ."Rules:\n"
                ."- Output ONLY the HTML body fragment.\n"
                ."- Do NOT output '<!DOCTYPE>', '<html>', '<head>', '<body>', '<title>', '<meta>', or any document-level tags.\n"
                ."- Do NOT wrap the response in Markdown code blocks (```html ... ```).\n"
                ."- Do NOT add explanations, introductions, or summaries before or after the HTML.\n"
                ."- Use only semantic tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a>, <blockquote>, <img>.\n"
                .'- Introduce the brand story, values, and product range; use bullet lists for key highlights.',
            'page' => "Generate page body content as clean HTML suitable for a WYSIWYG editor.\n"
                ."Rules:\n"
                ."- Output ONLY the HTML body fragment.\n"
                ."- Do NOT output '<!DOCTYPE>', '<html>', '<head>', '<body>', '<title>', '<meta>', or any document-level tags.\n"
                ."- Do NOT wrap the response in Markdown code blocks (```html ... ```).\n"
                ."- Do NOT add explanations, introductions, or summaries before or after the HTML.\n"
                ."- Use only semantic tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a>, <blockquote>, <img>.\n"
                .'- Structure the page with a clear headline (h2), sections (h3), and concise paragraphs.',
            default => "Generate article body content as clean HTML suitable for a WYSIWYG editor.\n"
                ."Rules:\n"
                ."- Output ONLY the HTML body fragment.\n"
                ."- Do NOT output '<!DOCTYPE>', '<html>', '<head>', '<body>', '<title>', '<meta>', or any document-level tags.\n"
                ."- Do NOT wrap the response in Markdown code blocks (```html ... ```).\n"
                ."- Do NOT add explanations, introductions, or summaries before or after the HTML.\n"
                ."- Use only semantic tags: <h2>, <h3>, <p>, <ul>, <ol>, <li>, <strong>, <em>, <a>, <blockquote>, <img>.\n"
                .'- Structure the article with a compelling headline (h2), clear sections (h3), and actionable takeaways.',
        };
    }

    /**
     * Clean raw AI output for rich-text fields: strip document-level tags, extract HTML from
     * Markdown code blocks, and remove leading/trailing explanations.
     */
    public static function cleanContent(string $text): string
    {
        $text = trim($text);
        if ($text === '') {
            return '';
        }

        // Extract HTML from Markdown code blocks if present
        if (preg_match('/```(?:html)?\s*(.*?)\s*```/s', $text, $matches)) {
            $text = trim($matches[1]);
        }

        // Remove document-level tags and everything outside <body> if a full HTML doc is returned
        if (stripos($text, '<body') !== false && preg_match('/<body[^>]*>(.*?)<\/body>/si', $text, $bodyMatches)) {
            $text = trim($bodyMatches[1]);
        }

        // Strip DOCTYPE, html, head, title, meta tags
        $text = preg_replace('/<!DOCTYPE[^>]*>/i', '', $text);
        $text = preg_replace('/<\/?(?:html|head|title|meta|body)[^>]*>/i', '', $text);

        // Remove common explanatory prefixes/suffixes in Chinese/English
        $prefixes = [
            '/^以下是[^：:]*[：:]\s*/u',
            '/^这是[^：:]*[：:]\s*/u',
            '/^以下是您所需的HTML正文内容[^：:]*[：:]\s*/u',
            '/^Here is [^：:.]*[:.]\s*/i',
            '/^Below is [^：:.]*[:.]\s*/i',
        ];
        foreach ($prefixes as $pattern) {
            $text = preg_replace($pattern, '', $text);
        }

        return trim($text);
    }
}
