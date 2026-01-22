<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

class ImageService
{
    private string $originImage;

    private string $image;

    private string $imagePath;

    private string $placeholderImage;

    const PLACEHOLDER_IMAGE = 'images/placeholder.png';

    /**
     * @throws Exception
     */
    public function __construct($image)
    {
        $this->originImage      = $image;
        $this->placeholderImage = system_setting('placeholder', self::PLACEHOLDER_IMAGE);
        if (! is_file(public_path($this->placeholderImage))) {
            $this->placeholderImage = self::PLACEHOLDER_IMAGE;
        }
        $this->image     = $image ?: $this->placeholderImage;
        $this->imagePath = public_path($this->image);

        // Check if file exists and is a valid image
        if (! is_file($this->imagePath)) {
            $this->image     = $this->placeholderImage;
            $this->imagePath = public_path($this->placeholderImage);
        } elseif (! $this->isValidImage($this->imagePath)) {
            // File exists but is not a valid image, use placeholder
            $this->image     = $this->placeholderImage;
            $this->imagePath = public_path($this->placeholderImage);
        }
    }

    /**
     * @param  $image
     * @return static
     * @throws Exception
     */
    public static function getInstance($image): self
    {
        return new self($image);
    }

    /**
     * Set plugin directory name
     *
     * @param  $dirName
     * @return $this
     */
    public function setPluginDirName($dirName): static
    {
        $originImage     = $this->originImage;
        $this->imagePath = plugin_path("{$dirName}/Public").$originImage;
        if (file_exists($this->imagePath)) {
            $this->image = strtolower('plugins/'.$dirName.$originImage);
        } else {
            $this->image     = $this->placeholderImage;
            $this->imagePath = public_path($this->image);
        }

        return $this;
    }

    /**
     * Generate thumbnail image with multiple resize modes
     *
     * @param  int  $width
     * @param  int  $height
     * @param  string|null  $mode  Resize mode: cover, contain, resize, pad, width-cover, height-cover
     *                             - cover: Maintain aspect ratio, crop to fill (default)
     *                             - contain: Maintain aspect ratio, fit within bounds (may have padding)
     *                             - resize: Stretch to exact dimensions (may distort)
     *                             - pad: Maintain aspect ratio, fill padding with background color
     *                             - width-cover: Stretch to width, crop/pad height
     *                             - height-cover: Stretch to height, crop/pad width
     * @return string
     */
    public function resize(int $width = 100, int $height = 100, ?string $mode = null): string
    {
        try {
            // Validate image file
            if (! $this->validateImageFile()) {
                return $this->originUrl();
            }

            // SVG files are vector graphics and don't need resizing
            // Return original URL directly
            $extension = strtolower(pathinfo($this->imagePath, PATHINFO_EXTENSION));
            if ($extension === 'svg') {
                return $this->originUrl();
            }

            // Get and validate image dimensions
            $dimensions = $this->getImageDimensions();
            if ($dimensions === null) {
                return $this->originUrl();
            }

            [$originalWidth, $originalHeight] = $dimensions;

            // Validate image size constraints
            if (! $this->validateImageDimensions($originalWidth, $originalHeight)) {
                return $this->originUrl();
            }

            // Validate memory availability
            if (! $this->validateMemory($originalWidth, $originalHeight, $width, $height)) {
                return $this->originUrl();
            }

            // Get and validate resize mode
            $mode = $this->getResizeMode($mode);

            // Generate cache filename
            $newImage     = $this->generateCacheFilename($width, $height, $mode);
            $newImagePath = public_path($newImage);

            // Process image if cache doesn't exist or source is newer
            if (! is_file($newImagePath) || (filemtime($this->imagePath) > filemtime($newImagePath))) {
                $this->processImage($newImagePath, $width, $height, $mode, $originalWidth, $originalHeight);
            }

            return asset($newImage);
        } catch (\Throwable $e) {
            Log::error('Image resize failed', [
                'path'   => $this->imagePath,
                'error'  => $e->getMessage(),
                'memory' => $this->formatBytes(memory_get_usage(true)),
            ]);

            return $this->originUrl();
        }
    }

    /**
     * Check if file is a valid image
     *
     * @param  string  $filePath
     * @return bool
     */
    private function isValidImage(string $filePath): bool
    {
        if (! is_file($filePath) || ! is_readable($filePath)) {
            return false;
        }

        // Check file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // SVG files need special handling as getimagesize() doesn't work well with SVG
        if ($extension === 'svg') {
            return $this->isValidSvg($filePath);
        }

        // For other image formats, use getimagesize() to validate
        $imageInfo = @getimagesize($filePath);

        return $imageInfo !== false;
    }

    /**
     * Check if file is a valid SVG
     *
     * @param  string  $filePath
     * @return bool
     */
    private function isValidSvg(string $filePath): bool
    {
        // Read first few bytes to check for SVG signature
        $handle = @fopen($filePath, 'r');
        if ($handle === false) {
            return false;
        }

        $content = @fread($handle, 1024);
        @fclose($handle);

        if ($content === false) {
            return false;
        }

        // Check if content contains SVG tag
        // SVG can be XML format with <?xml> or direct <svg> tag
        return preg_match('/<svg\b/i', $content) === 1 || preg_match('/<\?xml[^>]*>\s*<svg\b/i', $content) === 1;
    }

    /**
     * Validate image file exists and is readable
     *
     * @return bool
     */
    private function validateImageFile(): bool
    {
        return is_file($this->imagePath) && is_readable($this->imagePath);
    }

    /**
     * Get image dimensions
     *
     * @return array|null [width, height] or null on failure
     */
    private function getImageDimensions(): ?array
    {
        $imageInfo = @getimagesize($this->imagePath);
        if ($imageInfo === false) {
            Log::warning('Unable to get image size', ['path' => $this->imagePath]);

            return null;
        }

        return [$imageInfo[0], $imageInfo[1]];
    }

    /**
     * Validate image dimensions are within limits
     *
     * @param  int  $width
     * @param  int  $height
     * @return bool
     */
    private function validateImageDimensions(int $width, int $height): bool
    {
        $maxDimension = 5000;
        if ($width > $maxDimension || $height > $maxDimension) {
            Log::warning('Image too large to process', [
                'path'   => $this->imagePath,
                'width'  => $width,
                'height' => $height,
                'max'    => $maxDimension,
            ]);

            return false;
        }

        return true;
    }

    /**
     * Validate sufficient memory is available
     *
     * @param  int  $originalWidth
     * @param  int  $originalHeight
     * @param  int  $targetWidth
     * @param  int  $targetHeight
     * @return bool
     */
    private function validateMemory(int $originalWidth, int $originalHeight, int $targetWidth, int $targetHeight): bool
    {
        // Estimate memory: width * height * 4 bytes (RGBA) * 2 (source + destination)
        $estimatedMemory = ($originalWidth * $originalHeight * 4 * 2) + ($targetWidth * $targetHeight * 4);
        $memoryLimit     = $this->getMemoryLimit();
        $currentMemory   = memory_get_usage(true);
        $availableMemory = $memoryLimit - $currentMemory;

        // Require 20MB buffer
        $requiredMemory = $estimatedMemory + (20 * 1024 * 1024);
        if ($availableMemory < $requiredMemory) {
            Log::warning('Insufficient memory to process image', [
                'path'      => $this->imagePath,
                'required'  => $this->formatBytes($requiredMemory),
                'available' => $this->formatBytes($availableMemory),
            ]);

            return false;
        }

        return true;
    }

    /**
     * Get and validate resize mode
     *
     * @param  string|null  $mode
     * @return string
     */
    private function getResizeMode(?string $mode): string
    {
        if ($mode === null) {
            $mode = system_setting('image_resize_mode', 'cover');
        }

        $validModes = ['cover', 'contain', 'resize', 'pad', 'width-cover', 'height-cover'];
        if (! in_array($mode, $validModes)) {
            return 'cover';
        }

        return $mode;
    }

    /**
     * Generate cache filename
     *
     * @param  int  $width
     * @param  int  $height
     * @param  string  $mode
     * @return string
     */
    private function generateCacheFilename(int $width, int $height, string $mode): string
    {
        $extension = pathinfo($this->imagePath, PATHINFO_EXTENSION);
        $baseName  = mb_substr($this->image, 0, mb_strrpos($this->image, '.'));

        // Include pad color in cache filename if using pad mode
        $cacheSuffix = $mode;
        if ($mode === 'pad') {
            $padColor    = $this->getPadColor();
            $cacheSuffix = $mode.'-'.$padColor;
        }

        return "cache/{$baseName}-{$width}x{$height}-{$cacheSuffix}.{$extension}";
    }

    /**
     * Get pad color from settings and validate
     *
     * @return string Hex color without #
     */
    private function getPadColor(): string
    {
        $padColor = system_setting('image_pad_color', '#ffffff');
        $padColor = ltrim($padColor, '#');

        if (! preg_match('/^[0-9A-Fa-f]{6}$/', $padColor)) {
            return 'ffffff';
        }

        return $padColor;
    }

    /**
     * Process image with specified resize mode
     *
     * @param  string  $outputPath
     * @param  int  $width
     * @param  int  $height
     * @param  string  $mode
     * @param  int  $originalWidth
     * @param  int  $originalHeight
     * @return void
     */
    private function processImage(string $outputPath, int $width, int $height, string $mode, int $originalWidth, int $originalHeight): void
    {
        create_directories(dirname($outputPath));

        $manager = new ImageManager(new Driver);
        $image   = $manager->read($this->imagePath);

        $this->applyResizeMode($image, $mode, $width, $height, $originalWidth, $originalHeight);

        $image->save($outputPath);

        // Free memory
        unset($image, $manager);
    }

    /**
     * Apply resize mode to image
     *
     * @param  mixed  $image  Intervention Image instance
     * @param  string  $mode
     * @param  int  $width
     * @param  int  $height
     * @param  int  $originalWidth
     * @param  int  $originalHeight
     * @return void
     */
    private function applyResizeMode($image, string $mode, int $width, int $height, int $originalWidth, int $originalHeight): void
    {
        switch ($mode) {
            case 'contain':
                $image->contain($width, $height);
                break;

            case 'resize':
                $image->resize($width, $height);
                break;

            case 'pad':
                $this->applyPadMode($image, $width, $height, $originalWidth, $originalHeight);
                break;

            case 'width-cover':
                $this->applyWidthCoverMode($image, $width, $height, $originalWidth, $originalHeight);
                break;

            case 'height-cover':
                $this->applyHeightCoverMode($image, $width, $height, $originalWidth, $originalHeight);
                break;

            case 'cover':
            default:
                $image->cover($width, $height);
                break;
        }
    }

    /**
     * Apply pad mode: maintain aspect ratio, fill with background color
     *
     * @param  mixed  $image
     * @param  int  $width
     * @param  int  $height
     * @param  int  $originalWidth
     * @param  int  $originalHeight
     * @return void
     */
    private function applyPadMode($image, int $width, int $height, int $originalWidth, int $originalHeight): void
    {
        $ratio     = min($width / $originalWidth, $height / $originalHeight);
        $newWidth  = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);

        $image->resize($newWidth, $newHeight);

        $padColor = $this->getPadColor();
        $image->contain($width, $height, $padColor);
    }

    /**
     * Apply width-cover mode: stretch to target width, crop height to fit
     *
     * @param  mixed  $image
     * @param  int  $width
     * @param  int  $height
     * @param  int  $originalWidth
     * @param  int  $originalHeight
     * @return void
     */
    private function applyWidthCoverMode($image, int $width, int $height, int $originalWidth, int $originalHeight): void
    {
        // First, resize to target width while maintaining aspect ratio
        $widthRatio = $width / $originalWidth;
        $newHeight  = (int) ($originalHeight * $widthRatio);
        $image->resize($width, $newHeight);

        // Then crop height to target height (center crop)
        if ($newHeight > $height) {
            $image->cover($width, $height);
        } elseif ($newHeight < $height) {
            // If height is less than target, pad with background color
            $padColor = $this->getPadColor();
            $image->contain($width, $height, $padColor);
        }
    }

    /**
     * Apply height-cover mode: stretch to target height, crop width to fit
     *
     * @param  mixed  $image
     * @param  int  $width
     * @param  int  $height
     * @param  int  $originalWidth
     * @param  int  $originalHeight
     * @return void
     */
    private function applyHeightCoverMode($image, int $width, int $height, int $originalWidth, int $originalHeight): void
    {
        // First, resize to target height while maintaining aspect ratio
        $heightRatio = $height / $originalHeight;
        $newWidth    = (int) ($originalWidth * $heightRatio);
        $image->resize($newWidth, $height);

        // Then crop width to target width (center crop)
        if ($newWidth > $width) {
            $image->cover($width, $height);
        } elseif ($newWidth < $width) {
            // If width is less than target, pad with background color
            $padColor = $this->getPadColor();
            $image->contain($width, $height, $padColor);
        }
    }

    /**
     * Get PHP memory limit in bytes
     *
     * @return int
     */
    private function getMemoryLimit(): int
    {
        $memoryLimit = ini_get('memory_limit');
        if ($memoryLimit == -1) {
            return PHP_INT_MAX;
        }

        $memoryLimit = trim($memoryLimit);
        $last        = strtolower($memoryLimit[strlen($memoryLimit) - 1]);
        $value       = (int) $memoryLimit;

        switch ($last) {
            case 'g':
                $value *= 1024;
                // no break
            case 'm':
                $value *= 1024;
                // no break
            case 'k':
                $value *= 1024;
        }

        return $value;
    }

    /**
     * Format bytes to human readable format
     *
     * @param  int  $bytes
     * @return string
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow   = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow   = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }

    /**
     * Get original image url.
     *
     * @return string
     */
    public function originUrl(): string
    {
        return asset($this->image);
    }
}
