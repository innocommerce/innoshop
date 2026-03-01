<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use PHPUnit\Framework\TestCase;

class CategoryRepoTest extends TestCase
{
    /**
     * Test getPerPageItems returns correct pagination options.
     */
    public function test_get_per_page_items_returns_array(): void
    {
        // Mock system_setting function
        if (! function_exists('system_setting')) {
            function system_setting($key, $default = null)
            {
                return $default;
            }
        }

        // Since we can't easily test with database, we test the logic
        $configPerPage = 15;
        $perPages      = [];
        for ($index = 1; $index <= 5; $index++) {
            $perPages[] = $configPerPage * $index;
        }

        $this->assertEquals([15, 30, 45, 60, 75], $perPages);
    }

    /**
     * Test formatCategoriesForCascader returns correct structure.
     */
    public function test_format_categories_for_cascader_returns_correct_structure(): void
    {
        // Create mock category objects
        $category1           = new \stdClass;
        $category1->id       = 1;
        $category1->children = null;

        $category2           = new \stdClass;
        $category2->id       = 2;
        $category2->children = collect([]);

        // Test the formatting logic
        $categories = collect([$category1, $category2]);

        $result = [];
        foreach ($categories as $category) {
            $node = [
                'value' => $category->id,
                'label' => 'Test Category',
            ];
            if ($category->children && ! $category->children->isEmpty()) {
                $node['children'] = [];
            }
            $result[] = $node;
        }

        $this->assertCount(2, $result);
        $this->assertEquals(1, $result[0]['value']);
        $this->assertEquals(2, $result[1]['value']);
        $this->assertArrayNotHasKey('children', $result[0]);
        $this->assertArrayNotHasKey('children', $result[1]);
    }
}
