<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Unit\Helpers;

use InnoShop\Common\Models\Brand;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class LocaleFieldDataTest extends TestCase
{
    #[Test]
    public function it_falls_back_to_model_attribute_when_translation_is_missing(): void
    {
        (new Locale(['name' => 'English', 'code' => 'en', 'image' => 'images/flags/en.svg', 'position' => 0, 'active' => true]))->save();
        (new Locale(['name' => '简体中文', 'code' => 'zh-cn', 'image' => 'images/flags/zh-cn.svg', 'position' => 1, 'active' => true]))->save();

        $brand = new Brand;
        $brand->fill([
            'name'  => 'Nike',
            'first' => 'N',
            'logo'  => 'images/demo/brands/nike.png',
        ]);
        $brand->save();

        $data = locale_field_data($brand, 'name');

        $this->assertEquals('Nike', $data['en']);
        $this->assertEquals('Nike', $data['zh-cn']);
    }

    #[Test]
    public function it_prefers_translation_over_model_attribute(): void
    {
        (new Locale(['name' => 'English', 'code' => 'en', 'image' => 'images/flags/en.svg', 'position' => 0, 'active' => true]))->save();

        $brand = new Brand;
        $brand->fill([
            'name'  => 'Nike',
            'first' => 'N',
            'logo'  => 'images/demo/brands/nike.png',
        ]);
        $brand->save();
        $brand->translations()->create(['locale' => 'en', 'name' => 'Nike EN']);

        $data = locale_field_data($brand, 'name');

        $this->assertEquals('Nike EN', $data['en']);
    }
}
