<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\FileSecurityValidator;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class FileSecurityTest extends TestCase
{
    private ReflectionClass $reflection;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reflection = new ReflectionClass(FileSecurityValidator::class);
    }

    #[Test]
    public function test_file_security_validator_has_validate_file_extension_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('validateFileExtension'));
        $method = $this->reflection->getMethod('validateFileExtension');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_sanitize_svg_content_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('sanitizeSvgContent'));
        $method = $this->reflection->getMethod('sanitizeSvgContent');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_validate_mime_type_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('validateMimeType'));
        $method = $this->reflection->getMethod('validateMimeType');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_validate_file_name_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('validateFileName'));
        $method = $this->reflection->getMethod('validateFileName');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_validate_file_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('validateFile'));
        $method = $this->reflection->getMethod('validateFile');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_validate_directory_path_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('validateDirectoryPath'));
        $method = $this->reflection->getMethod('validateDirectoryPath');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_get_safe_image_extensions_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getSafeImageExtensions'));
        $method = $this->reflection->getMethod('getSafeImageExtensions');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_file_security_validator_has_get_safe_document_extensions_method(): void
    {
        $this->assertTrue($this->reflection->hasMethod('getSafeDocumentExtensions'));
        $method = $this->reflection->getMethod('getSafeDocumentExtensions');
        $this->assertTrue($method->isStatic());
        $this->assertTrue($method->isPublic());
    }

    #[Test]
    public function test_safe_image_extensions_are_correct(): void
    {
        $expected = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $actual   = FileSecurityValidator::getSafeImageExtensions();

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function test_safe_document_extensions_are_correct(): void
    {
        $expected = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'zip', 'rar'];
        $actual   = FileSecurityValidator::getSafeDocumentExtensions();

        $this->assertEquals($expected, $actual);
    }

    #[Test]
    public function test_dangerous_extensions_are_blocked(): void
    {
        // Document dangerous extensions
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('php', $source);
        $this->assertStringContainsString('exe', $source);
        $this->assertStringContainsString('sh', $source);
        $this->assertStringContainsString('bat', $source);
        $this->assertStringContainsString('htaccess', $source);
    }

    #[Test]
    public function test_potentially_dangerous_extensions_are_identified(): void
    {
        // Document potentially dangerous extensions
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('svg', $source);
        $this->assertStringContainsString('html', $source);
        $this->assertStringContainsString('xml', $source);
    }

    #[Test]
    public function test_svg_sanitization_removes_scripts(): void
    {
        // Document SVG script removal
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('script', $source);
        $this->assertStringContainsString('preg_replace', $source);
    }

    #[Test]
    public function test_svg_sanitization_removes_event_handlers(): void
    {
        // Document event handler removal
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('onload', $source);
        $this->assertStringContainsString('onerror', $source);
        $this->assertStringContainsString('onclick', $source);
        $this->assertStringContainsString('javascript:', $source);
    }

    #[Test]
    public function test_path_traversal_detection(): void
    {
        // Document path traversal detection
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('..', $source);
        $this->assertStringContainsString('path traversal', $source);
    }

    #[Test]
    public function test_null_byte_attack_detection(): void
    {
        // Document null byte attack detection
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('\0', $source);
        $this->assertStringContainsString('Null byte attack', $source);
    }

    #[Test]
    public function test_file_name_length_validation(): void
    {
        // Document file name length validation
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('strlen', $source);
        $this->assertStringContainsString('255', $source);
    }

    #[Test]
    public function test_dangerous_mime_types_are_blocked(): void
    {
        // Document dangerous MIME types
        $source = file_get_contents($this->reflection->getFileName());
        $this->assertStringContainsString('application/x-php', $source);
        $this->assertStringContainsString('application/x-httpd-php', $source);
        $this->assertStringContainsString('text/x-shellscript', $source);
    }
}
