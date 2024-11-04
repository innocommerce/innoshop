<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use InnoShop\Common\Models\Tag;

class TagSeeder extends Seeder
{
    public function run(): void
    {
        $items = $this->getTags();
        if ($items) {
            Tag::query()->truncate();
            foreach ($items as $item) {
                Tag::query()->create($item);
            }
        }

        $items = $this->getTagTranslations();
        if ($items) {
            Tag\Translation::query()->truncate();
            foreach ($items as $item) {
                Tag\Translation::query()->create($item);
            }
        }
    }

    /**
     * @return array[]
     */
    private function getTags(): array
    {
        return [
            [
                'id'       => 1,
                'slug'     => 'ecommerce',
                'position' => 1,
                'active'   => 1,
            ],
            [
                'id'       => 2,
                'slug'     => 'opensource',
                'position' => 2,
                'active'   => 1,
            ],
            [
                'id'       => 3,
                'slug'     => 'export',
                'position' => 2,
                'active'   => 1,
            ],
        ];
    }

    /**
     * @return array[]
     */
    private function getTagTranslations(): array
    {
        return
            [
                [
                    'tag_id' => 1,
                    'locale' => 'zh_cn',
                    'name'   => '电商',
                ],
                [
                    'tag_id' => 1,
                    'locale' => 'en',
                    'name'   => 'Ecommerce',
                ],
                [
                    'tag_id' => 2,
                    'locale' => 'zh_cn',
                    'name'   => '开源',
                ],
                [
                    'tag_id' => 2,
                    'locale' => 'en',
                    'name'   => 'Open Source',
                ],
                [
                    'tag_id' => 3,
                    'locale' => 'zh_cn',
                    'name'   => '外贸',
                ],
                [
                    'tag_id' => 3,
                    'locale' => 'en',
                    'name'   => 'Export',
                ],
            ];
    }
}
