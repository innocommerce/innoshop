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
use InnoShop\Common\Models\Option;
use InnoShop\Common\Models\OptionValue;

class OptionSeeder extends Seeder
{
    public function run(): void
    {
        OptionValue::query()->truncate();
        Option::query()->truncate();

        $options = $this->getOptions();

        foreach ($options as $index => $opt) {
            $option = Option::query()->create([
                'name'        => $opt['name'],
                'description' => $opt['description'],
                'type'        => $opt['type'],
                'position'    => $index,
                'active'      => true,
                'required'    => $opt['required'] ?? false,
            ]);

            $position = 0;
            foreach ($opt['values'] as $value) {
                OptionValue::query()->create([
                    'option_id' => $option->id,
                    'name'      => $value['name'],
                    'image'     => $value['image'] ?? '',
                    'position'  => $position++,
                    'active'    => true,
                ]);
            }
        }
    }

    private function getOptions(): array
    {
        return [
            [
                'name' => [
                    'zh-cn' => '包装服务',
                    'en'    => 'Gift Wrap',
                ],
                'description' => [
                    'zh-cn' => '为商品提供标准或高级礼盒包装',
                    'en'    => 'Provide standard or premium gift wrapping',
                ],
                'type'     => 'radio',
                'required' => true,
                'values'   => [
                    [
                        'name'  => ['zh-cn' => '标准包装', 'en' => 'Standard Wrap'],
                        'image' => '',
                    ],
                    [
                        'name'  => ['zh-cn' => '高级礼盒', 'en' => 'Premium Gift Box'],
                        'image' => '',
                    ],
                ],
            ],
            [
                'name' => [
                    'zh-cn' => '附加配件',
                    'en'    => 'Accessories',
                ],
                'description' => [
                    'zh-cn' => '可选配件提升整体效果',
                    'en'    => 'Optional accessories to enhance the product',
                ],
                'type'     => 'checkbox',
                'required' => false,
                'values'   => [
                    [
                        'name'  => ['zh-cn' => '胸针', 'en' => 'Brooch'],
                        'image' => 'images/demo/product/7.png',
                    ],
                    [
                        'name'  => ['zh-cn' => '腰带', 'en' => 'Belt'],
                        'image' => 'images/demo/product/8.png',
                    ],
                ],
            ],
            [
                'name' => [
                    'zh-cn' => '加急定制',
                    'en'    => 'Express Customization',
                ],
                'description' => [
                    'zh-cn' => '是否选择加急服务',
                    'en'    => 'Choose rush service option',
                ],
                'type'     => 'radio',
                'required' => false,
                'values'   => [
                    [
                        'name'  => ['zh-cn' => '不加急', 'en' => 'No Rush'],
                        'image' => '',
                    ],
                    [
                        'name'  => ['zh-cn' => '次日出货', 'en' => 'Next-day Shipping'],
                        'image' => '',
                    ],
                ],
            ],
        ];
    }
}
