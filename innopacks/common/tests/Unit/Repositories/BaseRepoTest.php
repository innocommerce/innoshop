<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Repositories;

use InnoShop\Common\Repositories\BaseRepo;
use PHPUnit\Framework\TestCase;

class BaseRepoTest extends TestCase
{
    /**
     * Test getInstance returns static instance.
     */
    public function test_get_instance_returns_static_instance(): void
    {
        // Create a concrete implementation for testing
        $repo = new class extends BaseRepo
        {
            protected string $model = \Illuminate\Database\Eloquent\Model::class;

            protected string $table = 'test_table';

            public function __construct()
            {
                // Skip parent constructor to avoid model validation
            }
        };

        $this->assertInstanceOf(BaseRepo::class, $repo);
    }

    /**
     * Test withActive sets filter correctly.
     */
    public function test_with_active_sets_filter(): void
    {
        $repo = new class extends BaseRepo
        {
            protected string $model = \Illuminate\Database\Eloquent\Model::class;

            protected string $table = 'test_table';

            public function __construct()
            {
                // Skip parent constructor
            }

            public function getFilters(): array
            {
                return $this->filters;
            }
        };

        $result = $repo->withActive();

        $this->assertSame($repo, $result);
        $this->assertTrue($repo->getFilters()['active']);
    }

    /**
     * Test withRelations merges relations correctly.
     */
    public function test_with_relations_merges_relations(): void
    {
        $repo = new class extends BaseRepo
        {
            protected string $model = \Illuminate\Database\Eloquent\Model::class;

            protected string $table = 'test_table';

            public function __construct()
            {
                // Skip parent constructor
            }

            public function getRelations(): array
            {
                return $this->relations;
            }
        };

        $result = $repo->withRelations(['category', 'brand']);

        $this->assertSame($repo, $result);
        $this->assertEquals(['category', 'brand'], $repo->getRelations());

        // Test merging additional relations
        $repo->withRelations(['tags']);
        $this->assertEquals(['category', 'brand', 'tags'], $repo->getRelations());
    }
}
