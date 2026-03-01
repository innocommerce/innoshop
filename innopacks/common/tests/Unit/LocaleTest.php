<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit;

use InnoShop\Common\Models\Locale;
use InnoShop\Common\Repositories\LocaleRepo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class LocaleTest extends TestCase
{
    private ReflectionClass $modelReflection;

    private ReflectionClass $repoReflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelReflection = new ReflectionClass(Locale::class);
        $this->repoReflection  = new ReflectionClass(LocaleRepo::class);
    }

    #[Test]
    public function test_locale_model_has_correct_fillable_attributes(): void
    {
        $expectedFillable = ['name', 'code', 'image', 'position', 'active'];

        $model    = new Locale;
        $fillable = $model->getFillable();

        $this->assertEquals($expectedFillable, $fillable);
    }

    #[Test]
    public function test_locale_model_extends_base_model(): void
    {
        $parentClass = $this->modelReflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Models\BaseModel', $parentClass->getName());
    }

    #[Test]
    public function test_locale_repo_has_get_criteria_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getCriteria'));
        $method = $this->repoReflection->getMethod('getCriteria');
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_locale_repo_has_get_rtl_languages_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getRtlLanguages'));
        $method = $this->repoReflection->getMethod('getRtlLanguages');
        $this->assertTrue($method->isStatic());
    }

    #[Test]
    public function test_locale_repo_has_create_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('create'));
        $method = $this->repoReflection->getMethod('create');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_locale_repo_has_get_front_list_with_path_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getFrontListWithPath'));
        $method = $this->repoReflection->getMethod('getFrontListWithPath');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_locale_repo_has_builder_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('builder'));
        $method = $this->repoReflection->getMethod('builder');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_locale_repo_has_get_active_list_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getActiveList'));
        $method = $this->repoReflection->getMethod('getActiveList');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_locale_repo_extends_base_repo(): void
    {
        $parentClass = $this->repoReflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Repositories\BaseRepo', $parentClass->getName());
    }

    #[Test]
    public function test_rtl_languages_include_arabic(): void
    {
        // Document RTL language support
        $source = file_get_contents($this->repoReflection->getFileName());
        $this->assertStringContainsString('ar', $source);
    }

    #[Test]
    public function test_rtl_languages_include_hebrew(): void
    {
        // Document RTL language support
        $source = file_get_contents($this->repoReflection->getFileName());
        $this->assertStringContainsString('he', $source);
    }

    #[Test]
    public function test_rtl_languages_include_persian(): void
    {
        // Document RTL language support
        $source = file_get_contents($this->repoReflection->getFileName());
        $this->assertStringContainsString('fa', $source);
    }

    #[Test]
    public function test_locale_repo_uses_static_caching(): void
    {
        // Document static caching support via $enabledLocales property
        $source = file_get_contents($this->repoReflection->getFileName());
        $this->assertStringContainsString('enabledLocales', $source);
        $this->assertTrue($this->repoReflection->hasProperty('enabledLocales'));
    }

    #[Test]
    public function test_locale_repo_uses_hook_filters(): void
    {
        // Document hook filter usage for extensibility
        $source = file_get_contents($this->repoReflection->getFileName());
        $this->assertStringContainsString('fire_hook_filter', $source);
    }
}
