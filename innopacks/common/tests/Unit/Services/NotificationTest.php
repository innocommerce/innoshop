<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\SmsService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class NotificationTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(SmsService::class);
    }

    #[Test]
    public function test_sms_service_has_send_verification_code_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('sendVerificationCode'));
        $method = $this->reflection->getMethod('sendVerificationCode');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_sms_service_has_verify_code_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('verifyCode'));
        $method = $this->reflection->getMethod('verifyCode');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_sms_service_has_delete_code_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('deleteCode'));
        $method = $this->reflection->getMethod('deleteCode');
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_send_verification_code_returns_void(): void
    {
        $method     = $this->reflection->getMethod('sendVerificationCode');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    #[Test]
    public function test_verify_code_returns_bool(): void
    {
        $method     = $this->reflection->getMethod('verifyCode');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('bool', $returnType->getName());
    }

    #[Test]
    public function test_delete_code_returns_void(): void
    {
        $method     = $this->reflection->getMethod('deleteCode');
        $returnType = $method->getReturnType();

        $this->assertNotNull($returnType);
        $this->assertEquals('void', $returnType->getName());
    }

    #[Test]
    public function test_sms_service_supports_multiple_gateways(): void
    {
        // Document supported SMS gateways
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('yunpian', $source);
        $this->assertStringContainsString('aliyun', $source);
        $this->assertStringContainsString('tencent', $source);
        $this->assertStringContainsString('huawei', $source);
        $this->assertStringContainsString('qiniu', $source);
    }

    #[Test]
    public function test_sms_service_uses_easy_sms(): void
    {
        // Document EasySms integration
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('EasySms', $source);
    }

    #[Test]
    public function test_sms_service_supports_verification_types(): void
    {
        // Document supported verification types
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('register', $source);
        $this->assertStringContainsString('login', $source);
        $this->assertStringContainsString('reset', $source);
    }

    #[Test]
    public function test_sms_service_has_configurable_code_length(): void
    {
        // Document configurable code length
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('sms_code_length', $source);
    }

    #[Test]
    public function test_sms_service_has_configurable_expiration(): void
    {
        // Document configurable expiration
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('sms_code_expire_minutes', $source);
    }

    #[Test]
    public function test_sms_service_uses_cryptographically_secure_random(): void
    {
        // Document secure random code generation
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('random_int', $source);
    }

    #[Test]
    public function test_sms_service_deletes_old_codes_before_sending(): void
    {
        // Document old code deletion
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('delete()', $source);
    }

    #[Test]
    public function test_sms_service_uses_hook_actions(): void
    {
        // Document hook action usage for extensibility
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('fire_hook_action', $source);
        $this->assertStringContainsString('service.sms.send', $source);
    }

    #[Test]
    public function test_sms_service_has_logging(): void
    {
        // Document logging support
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('Log::info', $source);
        $this->assertStringContainsString('Log::error', $source);
    }
}
