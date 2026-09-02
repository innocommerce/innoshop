<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Handlers\TranslationHandler;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TranslationHandlerTest extends TestCase
{
    #[Test]
    public function test_process_accepts_locale_keyed_translations_without_default_locale(): void
    {
        // Locale-keyed input (as merged by repo patch()) that does NOT contain
        // the system default locale must fall back to the first entry, not key 0.
        $result = TranslationHandler::process([
            'zh-cn' => ['locale' => 'zh-cn', 'name' => '测试标签'],
        ], ['name' => ['description']]);

        $this->assertSame('zh-cn', $result[0]['locale']);
        $this->assertSame('测试标签', $result[0]['name']);
    }

    #[Test]
    public function test_process_prefers_default_locale_for_autofill_source(): void
    {
        $result = TranslationHandler::process([
            'en'    => ['locale' => 'en', 'name' => 'Default Name'],
            'zh-cn' => ['locale' => 'zh-cn', 'name' => ''],
        ], []);

        $locales = collect($result)->keyBy('locale');
        $this->assertSame('Default Name', $locales['en']['name']);
    }
}
