<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\JpegEncoder;
use Intervention\Image\Encoders\PngEncoder;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Intervention\Image\Interfaces\EncoderInterface;

class ImageDownscaler
{
    /**
     * Longest edge, in pixels, allowed for a stored upload. Vendors routinely
     * send print-resolution artwork (6000–25000px, multi-MB) which then trips
     * ImageService's 5000px guard and makes image_resize() fall back to the
     * original file on every page render.
     */
    public const DEFAULT_MAX_EDGE = 2000;

    /**
     * Hard ceiling for the temporary memory_limit bump while working on a
     * single image. Keeps one oversized upload from taking the whole box down.
     */
    public const MEMORY_CEILING = '512M';

    /**
     * Formats that get a purpose-built encoder. Anything else keeps its own
     * format through Intervention's auto encoder.
     */
    protected const ENCODER_MAP = [
        'image/jpeg' => JpegEncoder::class,
        'image/png'  => PngEncoder::class,
        'image/webp' => WebpEncoder::class,
    ];

    /**
     * Shrink an image in place so its longest edge does not exceed $maxEdge.
     *
     * The file is left untouched — and false is returned — when there is
     * nothing to shrink, when the format cannot be re-encoded safely (SVG is
     * vector, animated GIF/WebP would lose their frames), or when memory is
     * too tight to decode the source. An upload must never fail because of
     * this, so every failure path is downgraded to a log line.
     *
     * @param  string  $path  Absolute path to the image file.
     * @param  int|null  $maxEdge  Falls back to the image_max_edge setting.
     * @return bool Whether the file was rewritten.
     */
    public static function shrink(string $path, ?int $maxEdge = null): bool
    {
        $path = (string) $path;
        if (! self::isShrinkableFile($path)) {
            return false;
        }

        $maxEdge = $maxEdge ?: (int) system_setting('image_max_edge', self::DEFAULT_MAX_EDGE);
        if ($maxEdge < 1) {
            return false;
        }

        $dimensions = @getimagesize($path);
        if ($dimensions === false) {
            return false;
        }
        [$width, $height] = $dimensions;

        if (max($width, $height) <= $maxEdge) {
            return false;
        }
        if (! self::ensureMemoryLimit($width, $height, $maxEdge, $path)) {
            return false;
        }

        try {
            $manager = new ImageManager(new Driver);
            $image   = $manager->decode($path);
            if ($image->isAnimated()) {
                return false;
            }

            $image->scaleDown($maxEdge, $maxEdge);
            $image->encode(self::encoderFor($dimensions['mime'] ?? ''))->save($path);
            unset($image, $manager);

            return true;
        } catch (\Throwable $e) {
            Log::warning('Image downscale failed', [
                'path'  => $path,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Whether the path points at a raster image we know how to re-encode.
     */
    protected static function isShrinkableFile(string $path): bool
    {
        if ($path === '' || ! is_file($path) || ! is_readable($path)) {
            return false;
        }

        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'bmp'], true);
    }

    /**
     * Pick an encoder matching the source format, or null to let Intervention
     * keep the original format.
     */
    protected static function encoderFor(string $mime): ?EncoderInterface
    {
        $encoder = self::ENCODER_MAP[$mime] ?? null;

        return match ($encoder) {
            JpegEncoder::class => new JpegEncoder(quality: 90, progressive: true),
            PngEncoder::class  => new PngEncoder,
            WebpEncoder::class => new WebpEncoder(quality: 90),
            default            => null,
        };
    }

    /**
     * Make sure this process can decode the source, raising memory_limit up to
     * the class ceiling when needed. Returns false when even the ceiling is
     * not enough — the caller then keeps the original file untouched.
     */
    protected static function ensureMemoryLimit(int $width, int $height, int $maxEdge, string $path): bool
    {
        // Decoded source (4 bytes/px) plus the scaled copy and a working buffer.
        $required = ($width * $height * 4) + ($maxEdge * $maxEdge * 4) + (20 * 1024 * 1024);

        $limit     = self::bytesFromShorthand(ini_get('memory_limit'));
        $requested = min(max($limit, $required), self::bytesFromShorthand(self::MEMORY_CEILING));

        if ($requested < $required) {
            Log::warning('Image downscale skipped: not enough memory', [
                'path'      => $path,
                'source'    => "{$width}x{$height}",
                'required'  => self::formatBytes($required),
                'available' => self::formatBytes($limit),
            ]);

            return false;
        }

        if ($requested > $limit) {
            @ini_set('memory_limit', (string) $requested);
        }

        return true;
    }

    /**
     * Convert a PHP shorthand byte value (256M, 1G, -1) to bytes.
     */
    protected static function bytesFromShorthand(string|false|null $value): int
    {
        $value = trim((string) $value);
        if ($value === '' || $value === '-1') {
            return PHP_INT_MAX;
        }

        $unit   = strtolower($value[strlen($value) - 1]);
        $number = (int) $value;

        return match ($unit) {
            'g'     => $number * 1024 ** 3,
            'm'     => $number * 1024 ** 2,
            'k'     => $number * 1024,
            default => $number,
        };
    }

    protected static function formatBytes(int $bytes): string
    {
        return round($bytes / 1048576, 1).'MB';
    }
}
