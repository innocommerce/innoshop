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
use InnoShop\Common\Models\TagTranslation;

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
            TagTranslation::query()->truncate();
            foreach ($items as $item) {
                TagTranslation::query()->create($item);
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
                    'id'     => 1,
                    'tag_id' => 1,
                    'locale' => 'zh_cn',
                    'name'   => '电商',
                ],
                [
                    'id'     => 2,
                    'tag_id' => 2,
                    'locale' => 'zh_cn',
                    'name'   => '开源',
                ],
                [
                    'id'     => 3,
                    'tag_id' => 3,
                    'locale' => 'zh_cn',
                    'name'   => '外贸',
                ],
            ];
    }
}
