<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\AI\AIServiceManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class AIServiceTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(AIServiceManager::class);
    }

    #[Test]
    public function test_ai_service_manager_is_singleton(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getInstance'));
        $method = $this->reflection->getMethod('getInstance');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_generate_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('generate'));
        $method = $this->reflection->getMethod('generate');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_stream_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('stream'));
        $method = $this->reflection->getMethod('stream');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_make_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('make'));
        $method = $this->reflection->getMethod('make');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_get_model_for_purpose_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getModelForPurpose'));
        $method = $this->reflection->getMethod('getModelForPurpose');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_get_available_models_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getAvailableModels'));
        $method = $this->reflection->getMethod('getAvailableModels');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_get_models_for_select_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getModelsForSelect'));
        $method = $this->reflection->getMethod('getModelsForSelect');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_is_model_enabled_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('isModelEnabled'));
        $method = $this->reflection->getMethod('isModelEnabled');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_ai_service_manager_has_reload_config_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('reloadConfig'));
        $method = $this->reflection->getMethod('reloadConfig');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_generate_method_returns_string(): void
    {
        $method     = $this->reflection->getMethod('generate');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('string', $returnType->getName());
    }

    #[Test]
    public function test_stream_method_returns_iterable(): void
    {
        $method     = $this->reflection->getMethod('stream');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('iterable', $returnType->getName());
    }

    #[Test]
    public function test_ai_service_supports_multiple_models(): void
    {
        // Document supported AI models
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('openai', $source);
        $this->assertStringContainsString('deepseek', $source);
        $this->assertStringContainsString('kimi', $source);
        $this->assertStringContainsString('doubao', $source);
        $this->assertStringContainsString('qianwen', $source);
        $this->assertStringContainsString('hunyuan', $source);
        $this->assertStringContainsString('anthropic', $source);
    }

    #[Test]
    public function test_ai_service_has_fallback_model_support(): void
    {
        // Document fallback model support
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('fallback_models', $source);
        $this->assertStringContainsString('getFallbackModel', $source);
    }

    #[Test]
    public function test_ai_service_uses_hook_filters(): void
    {
        // Document hook filter usage for extensibility
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('apply_filters', $source);
        $this->assertStringContainsString('ai.generate_prompt', $source);
        $this->assertStringContainsString('ai.generate_options', $source);
        $this->assertStringContainsString('ai.generate_result', $source);
    }

    #[Test]
    public function test_ai_service_has_logging(): void
    {
        // Document logging support
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('Log::info', $source);
        $this->assertStringContainsString('Log::error', $source);
    }
}
