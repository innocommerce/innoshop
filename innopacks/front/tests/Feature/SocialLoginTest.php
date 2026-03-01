<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Front\Tests\Feature;

use InnoShop\Common\Models\Customer\Social;
use InnoShop\Common\Repositories\Customer\SocialRepo;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SocialLoginTest extends TestCase
{
    private ReflectionClass $modelReflection;

    private ReflectionClass $repoReflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->modelReflection = new ReflectionClass(Social::class);
        $this->repoReflection  = new ReflectionClass(SocialRepo::class);
    }

    #[Test]
    public function test_social_model_uses_correct_table(): void
    {
        $model = new Social;
        $this->assertEquals('customer_socials', $model->getTable());
    }

    #[Test]
    public function test_social_model_has_correct_fillable_attributes(): void
    {
        $expectedFillable = [
            'customer_id', 'provider', 'user_id', 'union_id', 'access_token', 'refresh_token', 'reference',
        ];

        $model    = new Social;
        $fillable = $model->getFillable();

        $this->assertEquals($expectedFillable, $fillable);
    }

    #[Test]
    public function test_social_model_has_customer_relationship(): void
    {
        $this->assertTrue($this->modelReflection->hasMethod('customer'));
        $method = $this->modelReflection->getMethod('customer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_social_model_extends_base_model(): void
    {
        $parentClass = $this->modelReflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Models\BaseModel', $parentClass->getName());
    }

    #[Test]
    public function test_social_repo_has_get_providers_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getProviders'));
        $method = $this->repoReflection->getMethod('getProviders');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_social_repo_has_init_social_config_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('initSocialConfig'));
        $method = $this->repoReflection->getMethod('initSocialConfig');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_social_repo_has_create_customer_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('createCustomer'));
        $method = $this->repoReflection->getMethod('createCustomer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_social_repo_has_create_social_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('createSocial'));
        $method = $this->repoReflection->getMethod('createSocial');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_social_repo_has_get_social_by_provider_and_user_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getSocialByProviderAndUser'));
        $method = $this->repoReflection->getMethod('getSocialByProviderAndUser');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_social_repo_has_get_social_by_provider_and_customer_method(): void
    {
        $this->assertTrue($this->repoReflection->hasMethod('getSocialByProviderAndCustomer'));
        $method = $this->repoReflection->getMethod('getSocialByProviderAndCustomer');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_get_providers_returns_array(): void
    {
        $method     = $this->repoReflection->getMethod('getProviders');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('array', $returnType->getName());
    }

    #[Test]
    public function test_supported_providers_include_major_platforms(): void
    {
        // Document expected providers
        $source = file_get_contents($this->repoReflection->getFileName());
        $this->assertStringContainsString('facebook', $source);
        $this->assertStringContainsString('twitter', $source);
        $this->assertStringContainsString('google', $source);
    }

    #[Test]
    public function test_social_repo_extends_base_repo(): void
    {
        $parentClass = $this->repoReflection->getParentClass();
        $this->assertNotFalse($parentClass);
        $this->assertEquals('InnoShop\Common\Repositories\BaseRepo', $parentClass->getName());
    }

    #[Test]
    public function test_customer_relationship_returns_belongs_to(): void
    {
        $method     = $this->modelReflection->getMethod('customer');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('Illuminate\Database\Eloquent\Relations\BelongsTo', $returnType->getName());
    }
}
