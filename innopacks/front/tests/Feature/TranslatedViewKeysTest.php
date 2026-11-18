<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * A translation key printed as plain text instead of being translated shows
 * the raw key to the customer ("front/order.confirm"). The views of this pack
 * must always go through the __() helper for a translated label.
 */
class TranslatedViewKeysTest extends TestCase
{
    #[Test]
    public function no_view_prints_a_raw_translation_key(): void
    {
        $root = dirname(__DIR__, 2).'/resources/views';

        $this->assertDirectoryExists($root);

        $views = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root));
        $this->assertGreaterThan(0, iterator_count(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($root))));

        foreach ($views as $view) {
            if (! $view->isFile() || $view->getExtension() !== 'php') {
                continue;
            }

            $contents = file_get_contents($view->getPathname());

            // "{{ 'some/key' }}" printed without the __() helper.
            $this->assertDoesNotMatchRegularExpression(
                '/\{\{\s*\'[a-z0-9_-]+\/[a-z0-9_.-]+\'\s*(\|[^}]*)?\}\}/i',
                $contents,
                $view->getPathname().' prints a raw translation key'
            );
        }
    }
}
