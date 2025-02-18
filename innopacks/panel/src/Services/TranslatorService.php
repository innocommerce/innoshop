<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Services;

use Exception;
use InnoShop\Panel\Interfaces\Translator;

class TranslatorService extends BaseService
{
    /**
     * @param  string  $source
     * @param  array|string  $targets
     * @param  array|string  $text
     * @return array
     * @throws Exception
     */
    public static function translate(string $source, array|string $targets, array|string $text): array
    {
        if (empty($source) || empty($targets) || empty($text)) {
            return [];
        }

        $translator = self::getTranslator();
        $targets    = self::handleTargets($targets, $source);

        $items = [];
        foreach ($targets as $target) {
            try {
                $result = $error = '';
                if (is_array($text)) {
                    $result = $translator->batchTranslate($source, $target, $text);
                } elseif (is_string($text)) {
                    $result = $translator->translate($source, $target, $text);
                }
                $result = addslashes(str_replace('’', '\'', $result));
            } catch (Exception $e) {
                $error = $e->getMessage();
            }
            $item = [
                'locale' => $target,
                'result' => $result,
                'error'  => $error,
            ];
            $items[] = $item;
        }

        return $items;
    }

    /**
     * Get translator.
     *
     * @return Translator|null
     * @throws Exception
     */
    private static function getTranslator(): ?Translator
    {
        $translatorName = fire_hook_filter('panel.service.translator', '');
        if (empty($translatorName)) {
            throw new Exception('Empty Translator');
        } elseif (! class_exists($translatorName)) {
            throw new Exception($translatorName.' Not Found');
        }

        $translator = new $translatorName;
        if (! $translator instanceof Translator) {
            throw new Exception("$translatorName should implement ".Translator::class);
        }

        return $translator;
    }

    /**
     * @param  $targets
     * @param  $source
     * @return array
     * @throws Exception
     */
    private static function handleTargets($targets, $source): array
    {
        if ($targets == 'all') {
            $targets = collect(locales())->where('code', '<>', $source)->pluck('code')->toArray();
        } elseif (is_string($targets)) {
            $targets = [$targets];
        }

        return $targets;
    }
}
