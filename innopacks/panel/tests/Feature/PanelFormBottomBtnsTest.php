<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Issue #376 — the bottom buttons of a panel form rendered hardcoded Chinese
 * labels ("提交保存" / "返回") while their sibling right-btns component uses
 * the translated keys (`panel/common.btn_submit` / `panel/common.btn_back`).
 *
 * This is a pure markup problem, so it is pinned down at the source level (no
 * Laravel app needed, the panel test suite is DB free).
 */
class PanelFormBottomBtnsTest extends TestCase
{
    private function componentSource(): string
    {
        $path = dirname(__DIR__, 2).'/resources/views/components/form/bottom-btns.blade.php';

        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }

    #[Test]
    public function bottom_buttons_use_the_translated_labels(): void
    {
        $source = $this->componentSource();

        $this->assertStringContainsString("__('panel/common.btn_submit')", $source);
        $this->assertStringContainsString("__('panel/common.btn_back')", $source);
    }

    #[Test]
    public function bottom_buttons_have_no_hardcoded_chinese_label(): void
    {
        $source = $this->componentSource();

        // Strip blade comments, then look for CJK characters.
        $visible = preg_replace('/\{\{--.*?--\}\}/s', '', $source);

        $this->assertDoesNotMatchRegularExpression('/[\x{4e00}-\x{9fff}]/u', (string) $visible);
    }
}
