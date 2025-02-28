<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Plugin\Youdao\Services;

use Exception;
use InnoShop\Panel\Interfaces\Translator;
use Plugin\Youdao\Libraries\Youdao;

class YoudaoService implements Translator
{
    private Youdao $translator;

    /**
     * @throws Exception
     */
    public function __construct()
    {
        $appKey           = plugin_setting('youdao.app_key', '');
        $appSecret        = plugin_setting('youdao.app_secret', '');
        $this->translator = new Youdao($appKey, $appSecret);
    }

    /**
     * @throws Exception
     */
    public function translate($from, $to, $text): string
    {
        $from = $this->mapCode($from);
        $to   = $this->mapCode($to);

        return $this->translator->translate($text, $from, $to);
    }

    /**
     * 批量翻译
     *
     * @param  $from
     * @param  $to
     * @param  $texts
     * @return array
     * @throws Exception
     */
    public function batchTranslate($from, $to, $texts): array
    {
        $from = $this->mapCode($from);
        $to   = $this->mapCode($to);

        return $this->translator->translateBatch($texts, $from, $to);
    }

    /**
     * @param  $code
     * @return string
     */
    public function mapCode($code): string
    {
        $map = [
            'ar'    => 'ar',
            'de'    => 'de',
            'en'    => 'en',
            'es'    => 'es',
            'fr'    => 'fr',
            'id'    => 'id',
            'it'    => 'it',
            'ja'    => 'ja',
            'ko'    => 'ko',
            'ms'    => 'ms',
            'pt'    => 'pt',
            'ru'    => 'ru',
            'th'    => 'th',
            'vi'    => 'vi',
            'zh_cn' => 'zh-CHS',
            'zh_hk' => 'zh-CHT',
        ];

        return $map[$code] ?? 'en';
    }
}
