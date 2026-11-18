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
 * Issue #378 — the title row of a panel form row nests a bootstrap `.row`
 * inside `.form-row`. Bootstrap gives `.row` a negative horizontal margin
 * (calc(var(--bs-gutter-x) * -.5) = -12px) while the panel stylesheet adds no
 * compensating padding, so the row overflowed its container by 12px.
 *
 * The gutter is reset with the `g-0` utility instead of padding `.form-row`:
 * padding the whole `.form-row` would also shift the form fields (which are
 * not inside a `.row`) to the right and break the label / field alignment,
 * whereas `g-0` keeps every text position untouched and only removes the
 * overflow.
 *
 * This is a pure markup problem, so it is pinned down at the source level (no
 * Laravel app needed, the panel test suite is DB free).
 */
class PanelFormRowTest extends TestCase
{
    private function componentSource(): string
    {
        $path = dirname(__DIR__, 2).'/resources/views/components/form/row.blade.php';

        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }

    #[Test]
    public function form_row_title_uses_a_gutterless_row(): void
    {
        $source = $this->componentSource();

        $this->assertStringContainsString('<div class="row g-0">', $source);
    }

    #[Test]
    public function form_row_has_no_bootstrap_row_without_gutter_reset(): void
    {
        $source = $this->componentSource();

        // A bare `.row` inside `.form-row` overflows the container by 12px.
        $this->assertDoesNotMatchRegularExpression('/<div class="row">/', $source);
    }
}
