<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\MediaUrlResolver;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class MediaUrlResolverTest extends TestCase
{
    #[Test]
    public function is_media_reference_detects_prefix_correctly(): void
    {
        $this->assertTrue(MediaUrlResolver::isMediaReference('media://123'));
        $this->assertTrue(MediaUrlResolver::isMediaReference('media://9999'));

        $this->assertFalse(MediaUrlResolver::isMediaReference(null));
        $this->assertFalse(MediaUrlResolver::isMediaReference(''));
        $this->assertFalse(MediaUrlResolver::isMediaReference('static/media/foo.jpg'));
        $this->assertFalse(MediaUrlResolver::isMediaReference('https://cdn.example.com/foo.jpg'));
        $this->assertFalse(MediaUrlResolver::isMediaReference('media:/123'));
        $this->assertFalse(MediaUrlResolver::isMediaReference('media:123'));
    }

    #[Test]
    public function extract_media_id_returns_numeric_id(): void
    {
        $this->assertSame(123, MediaUrlResolver::extractMediaId('media://123'));
        $this->assertSame(0, MediaUrlResolver::extractMediaId('media://0'));
        $this->assertNull(MediaUrlResolver::extractMediaId('media://abc'));
        $this->assertNull(MediaUrlResolver::extractMediaId('foo/bar.jpg'));
    }

    #[Test]
    public function build_reference_round_trips_with_extract(): void
    {
        $ref = MediaUrlResolver::buildReference(42);
        $this->assertSame('media://42', $ref);
        $this->assertSame(42, MediaUrlResolver::extractMediaId($ref));
    }

    #[Test]
    public function media_reference_prefix_constant_is_stable(): void
    {
        // Safeguard: changing this constant would silently break every stored reference.
        $this->assertSame('media://', MediaUrlResolver::MEDIA_REFERENCE_PREFIX);
    }
}
