<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Unit\Services;

use InnoShop\Common\Services\ImageDownscaler;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImageDownscalerTest extends TestCase
{
    private string $workDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->workDir = sys_get_temp_dir().'/innoshop_downscaler_'.uniqid();
        if (! is_dir($this->workDir)) {
            mkdir($this->workDir, 0777, true);
        }
    }

    protected function tearDown(): void
    {
        $this->rmdir($this->workDir);
        parent::tearDown();
    }

    #[Test]
    public function it_shrinks_an_oversized_jpeg(): void
    {
        $path = $this->createJpeg(6000, 10);

        $result = ImageDownscaler::shrink($path, 2000);

        [$width, $height] = getimagesize($path);
        $this->assertTrue($result);
        $this->assertLessThanOrEqual(2000, $width);
        $this->assertSame(IMAGETYPE_JPEG, exif_imagetype($path));
        $this->assertGreaterThan(0, $height);
    }

    #[Test]
    public function it_leaves_images_within_the_limit_untouched(): void
    {
        $path  = $this->createJpeg(800, 600);
        $bytes = file_get_contents($path);

        $result = ImageDownscaler::shrink($path, 2000);

        $this->assertFalse($result);
        $this->assertSame($bytes, file_get_contents($path));
    }

    #[Test]
    public function it_skips_formats_that_cannot_be_re_encoded(): void
    {
        $svg = $this->workDir.'/vector.svg';
        file_put_contents($svg, '<svg xmlns="http://www.w3.org/2000/svg" width="9000" height="9000"></svg>');

        $text = $this->workDir.'/not-an-image.txt';
        file_put_contents($text, 'definitely not an image');

        $this->assertFalse(ImageDownscaler::shrink($svg, 2000));
        $this->assertFileExists($svg);

        $this->assertFalse(ImageDownscaler::shrink($text, 2000));
        $this->assertSame('definitely not an image', file_get_contents($text));
    }

    #[Test]
    public function it_skips_missing_files(): void
    {
        $this->assertFalse(ImageDownscaler::shrink($this->workDir.'/nope.jpg', 2000));
    }

    #[Test]
    public function it_falls_back_to_the_configured_max_edge(): void
    {
        $path = $this->createJpeg(1500, 1500);

        // No explicit limit: the image_max_edge setting (default 2000) applies.
        $this->assertFalse(ImageDownscaler::shrink($path));

        [$width] = getimagesize($path);
        $this->assertSame(1500, $width);
    }

    /**
     * The actual payoff: an oversized upload used to be un-resizable, so
     * image_resize() served the multi-MB original on every page render.
     */
    #[Test]
    public function it_lets_image_resize_thumbnail_an_oversized_upload(): void
    {
        $relative = 'static/media/suppliers/testdownscaler/logo/banner.jpg';
        $absolute = public_path($relative);
        if (! is_dir(dirname($absolute))) {
            mkdir(dirname($absolute), 0777, true);
        }
        copy($this->createJpeg(9000, 10), $absolute);

        $this->assertStringNotContainsString('cache/', image_resize($relative, 100, 100));

        $this->assertTrue(ImageDownscaler::shrink($absolute, 2000));

        $thumbnail = image_resize($relative, 100, 100);
        $this->assertStringContainsString('cache/', $thumbnail);
        $this->assertStringContainsString('100x100', $thumbnail);
        $this->assertFileExists(public_path(parse_url($thumbnail, PHP_URL_PATH)));

        unlink($absolute);
        $this->rmdir(public_path('cache/static/media/suppliers/testdownscaler'));
        $this->rmdir(public_path('static/media/suppliers/testdownscaler'));
    }

    /**
     * Wide fixture that is only oversized on one edge — the shape vendors
     * actually upload (panoramic factory shots).
     */
    private function createJpeg(int $width, int $height): string
    {
        $path = $this->workDir."/fixture_{$width}x{$height}.jpg";

        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, imagecolorallocate($canvas, 200, 30, 30));
        imagejpeg($canvas, $path, 90);
        imagedestroy($canvas);

        return $path;
    }

    private function rmdir(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir.'/'.$item;
            if (is_dir($path)) {
                $this->rmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
