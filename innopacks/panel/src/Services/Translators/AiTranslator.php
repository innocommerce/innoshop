<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Services\Translators;

use Exception;
use InnoShop\Aicore\Services\AIServiceManager;
use InnoShop\Panel\Interfaces\Translator;

/**
 * AI translator engine backed by the provider configured in the panel
 * (后台 → 设置 → AI). Unlike the OpenAI plugin it needs no extra API key:
 * it reuses whichever text model the merchant already enabled.
 */
class AiTranslator implements Translator
{
    /**
     * Default system prompt shared by single and batch requests.
     */
    public const SYSTEM_PROMPT = 'You are a professional e-commerce translator specializing in product, category and CMS content. '
        .'Translate faithfully and naturally, keeping the original formatting. Never add explanations, notes or quotes.';

    /**
     * Max strings per batch request, kept small so long HTML stays in context.
     */
    private const BATCH_SIZE = 20;

    private bool $isHtml;

    public function __construct()
    {
        $this->isHtml = registry('translation_type', 'text') === 'html';
    }

    /**
     * Translate a single text.
     *
     * @param  $from
     * @param  $to
     * @param  $text
     * @return string
     * @throws Exception
     */
    public function translate($from, $to, $text): string
    {
        $text = (string) $text;
        if (trim($text) === '') {
            return '';
        }

        return $this->normalize($this->request($this->singlePrompt($from, $to, $text)));
    }

    /**
     * Translate a batch of texts with one request per chunk: JSON array in,
     * JSON array out with identical length and order.
     *
     * @param  $from
     * @param  $to
     * @param  $texts
     * @return array
     * @throws Exception
     */
    public function batchTranslate($from, $to, $texts): array
    {
        $texts = array_values((array) $texts);
        if (empty($texts)) {
            return [];
        }

        $results = [];
        foreach (array_chunk($texts, self::BATCH_SIZE) as $chunk) {
            $results = array_merge($results, $this->translateChunk($from, $to, $chunk));
        }

        return $results;
    }

    /**
     * Map InnoShop locale codes to human-readable language names for the prompt.
     * Codes not listed here are passed through as-is.
     */
    public function mapCode($code): string
    {
        $map = [
            'zh-cn' => 'Simplified Chinese',
            'zh-tw' => 'Traditional Chinese',
            'zh-hk' => 'Traditional Chinese (Hong Kong)',
            'en'    => 'English',
            'ja'    => 'Japanese',
            'ko'    => 'Korean',
            'fr'    => 'French',
            'de'    => 'German',
            'es'    => 'Spanish',
            'it'    => 'Italian',
            'pt'    => 'Portuguese',
            'ru'    => 'Russian',
            'ar'    => 'Arabic',
            'he'    => 'Hebrew',
            'fa'    => 'Persian (Farsi)',
            'th'    => 'Thai',
            'tr'    => 'Turkish',
            'vi'    => 'Vietnamese',
            'id'    => 'Indonesian',
            'ms'    => 'Malay',
            'nl'    => 'Dutch',
            'pl'    => 'Polish',
            'uk'    => 'Ukrainian',
        ];

        return $map[$code] ?? (string) $code;
    }

    /**
     * Prompt for a single translation: plain text in, plain text out.
     */
    private function singlePrompt($from, $to, string $text): string
    {
        return <<<PROMPT
Translate the following text from {$this->mapCode($from)} to {$this->mapCode($to)}.

Output requirements (single translation):
- Return ONLY the translated text itself — no explanations, no surrounding quotes, no markdown, no labels.
- {$this->formatRule()}

Text:
{$text}
PROMPT;
    }

    /**
     * Prompt for a batch translation: JSON array in, JSON array out.
     */
    private function batchPrompt($from, $to, array $texts): string
    {
        $json = json_encode(array_values($texts), JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Translate every string in the following JSON array from {$this->mapCode($from)} to {$this->mapCode($to)}.

Output requirements (batch translation):
- Return ONLY a valid JSON array of translated strings.
- The SAME length and the SAME order as the input: output[i] must be the translation of input[i].
- No markdown code fence, no explanations, no comments, no extra keys.
- {$this->formatRule()}

Input:
{$json}
PROMPT;
    }

    /**
     * Extra rules for HTML content, where tags and attributes must survive.
     */
    private function formatRule(): string
    {
        return $this->isHtml
            ? 'The strings contain HTML. Keep every HTML tag, attribute and placeholder exactly as-is, only translate the human-readable text nodes.'
            : 'Keep placeholders (e.g. :name, {id}, %s) exactly as-is.';
    }

    /**
     * Translate one chunk, falling back to per-item calls if the model
     * returns a misaligned array.
     */
    private function translateChunk($from, $to, array $chunk): array
    {
        try {
            $result = $this->parseJsonArray($this->request($this->batchPrompt($from, $to, $chunk)));
        } catch (Exception $e) {
            $result = [];
        }

        if (count($result) === count($chunk)) {
            return $result;
        }

        return array_map(fn ($text) => $this->translate($from, $to, $text), $chunk);
    }

    /**
     * Extract a JSON array from the model output (tolerates code fences / stray text).
     */
    private function parseJsonArray(string $output): array
    {
        $output  = trim($output);
        $decoded = json_decode($output, true);

        if (! is_array($decoded)) {
            $start = strpos($output, '[');
            $end   = strrpos($output, ']');
            if ($start !== false && $end !== false && $end > $start) {
                $decoded = json_decode(substr($output, $start, $end - $start + 1), true);
            }
        }

        if (! is_array($decoded) || ! array_is_list($decoded)) {
            throw new Exception('AI translator returned an invalid JSON array');
        }

        return array_map(fn ($item) => is_string($item) ? $item : (string) json_encode($item, JSON_UNESCAPED_UNICODE), $decoded);
    }

    /**
     * Trim artefacts the models like to add around a single translation.
     */
    private function normalize(string $result): string
    {
        $result = trim($result);
        if (strlen($result) > 1) {
            $first = $result[0];
            $last  = substr($result, -1);
            if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
                $result = substr($result, 1, -1);
            }
            if (str_starts_with($result, '```') && str_ends_with($result, '```')) {
                $result = trim(substr($result, 3, -3));
                $result = preg_replace('/^[a-zA-Z]+/', '', $result) ?? $result;
                $result = trim($result);
            }
        }

        return trim($result);
    }

    /**
     * Raw call to the AI model configured in the panel.
     */
    protected function callModel(string $prompt): string
    {
        return AIServiceManager::getInstance()->generate($prompt, 'translation', [
            'system_prompt' => self::SYSTEM_PROMPT,
        ]);
    }

    /**
     * Call the model and make sure something usable came back.
     *
     * @throws Exception When the provider/model is missing, unreachable or refuses
     */
    protected function request(string $prompt): string
    {
        $result = $this->callModel($prompt);

        if (trim($result) === '') {
            throw new Exception('AI translator returned an empty result, check the AI provider/model in 后台 → 设置 → AI.');
        }

        return $result;
    }
}
