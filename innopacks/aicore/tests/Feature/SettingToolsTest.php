<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Tests\Feature;

use InnoShop\Aicore\Tools\SettingDetailTool;
use InnoShop\Aicore\Tools\SettingUpdateTool;
use InnoShop\Common\Models\Locale;
use InnoShop\Common\Models\Setting;
use InnoShop\Common\Repositories\LocaleRepo;
use InnoShop\Common\Repositories\SettingRepo;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use ReflectionProperty;
use Tests\TestCase;

class SettingToolsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Setting::query()->where('space', 'system')->whereIn('name', SettingDetailTool::META_FIELDS)->delete();
        $this->refreshSettings();
    }

    private function refreshSettings(): void
    {
        config(['inno' => SettingRepo::getInstance()->groupedSettings()]);
    }

    private function storedValue(string $field): array
    {
        $row = Setting::query()->where('space', 'system')->where('name', $field)->first();

        return $row && $row->json ? json_decode($row->value, true) : [];
    }

    #[Test]
    public function test_detail_returns_empty_maps_when_unset(): void
    {
        $result = (new SettingDetailTool)->execute([]);

        foreach (SettingDetailTool::META_FIELDS as $field) {
            $this->assertArrayHasKey('en', $result[$field]);
            $this->assertSame('', $result[$field]['en']);
        }
    }

    #[Test]
    public function test_update_sets_default_locale_scalar(): void
    {
        $result = (new SettingUpdateTool)->execute([
            'meta_title'       => 'Diesel & Electric Forklift Manufacturer | VORTEX Forklifts',
            'meta_description' => 'VORTEX diesel and electric forklifts.',
        ]);

        $this->assertSame('Diesel & Electric Forklift Manufacturer | VORTEX Forklifts', $result['meta_title']['en']);
        $this->assertSame('VORTEX diesel and electric forklifts.', $result['meta_description']['en']);

        $stored = $this->storedValue('meta_title');
        $this->assertSame('Diesel & Electric Forklift Manufacturer | VORTEX Forklifts', $stored['en']);
    }

    #[Test]
    public function test_update_merges_locale_and_preserves_others(): void
    {
        SettingRepo::getInstance()->updateSystemValue('meta_title', ['en' => 'Old Title']);
        $this->refreshSettings();

        $result = (new SettingUpdateTool)->execute([
            'translations' => ['zh-cn' => ['meta_title' => 'VORTEX 叉车厂家']],
        ]);

        $this->assertSame('Old Title', $result['meta_title']['en'], 'Existing en value must survive');
        $this->assertSame('VORTEX 叉车厂家', $result['meta_title']['zh-cn']);

        $stored = $this->storedValue('meta_title');
        $this->assertSame('Old Title', $stored['en']);
        $this->assertSame('VORTEX 叉车厂家', $stored['zh-cn']);
    }

    #[Test]
    public function test_update_clears_value_with_empty_string(): void
    {
        SettingRepo::getInstance()->updateSystemValue('meta_keywords', ['en' => 'forklift, diesel']);
        $this->refreshSettings();

        $result = (new SettingUpdateTool)->execute(['meta_keywords' => '']);

        $this->assertSame('', $result['meta_keywords']['en']);
    }

    #[Test]
    public function test_update_throws_without_any_field(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new SettingUpdateTool)->execute([]);
    }

    #[Test]
    public function test_update_ignores_disabled_locale(): void
    {
        // This project seeds all locales (fr included), so disable fr and
        // reset LocaleRepo's static cache to build the scenario the filter
        // relies on. Cache is restored afterwards — it is process-wide and
        // would otherwise leak into later tests.
        Locale::query()->where('code', 'fr')->update(['active' => false]);
        $cache = new ReflectionProperty(LocaleRepo::class, 'enabledLocales');
        $cache->setValue(null, null);

        try {
            $result = (new SettingUpdateTool)->execute([
                'meta_title'   => 'English Title',
                'translations' => ['fr' => ['meta_title' => 'Titre']],
            ]);

            $this->assertArrayNotHasKey('fr', $result['meta_title']);
            $stored = $this->storedValue('meta_title');
            $this->assertSame('English Title', $stored['en']);
            $this->assertArrayNotHasKey('fr', $stored);
        } finally {
            $cache->setValue(null, null);
        }
    }
}
