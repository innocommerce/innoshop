<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Aicore\Services;

use Illuminate\Support\Facades\Storage;

class MediaImageResolver
{
    private static ?MediaImageResolver $instance = null;

    private function __construct() {}

    /**
     * @return MediaImageResolver
     */
    public static function getInstance(): MediaImageResolver
    {
        if (! self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Resolve only files inside the public media disk. Local paths and symlinks
     * must never become an arbitrary-file attachment for an AI provider.
     */
    public function resolveLocalImagePath(string $reference): ?string
    {
        if ($reference === '' || str_contains($reference, "\0")) {
            return null;
        }

        $candidates = [$reference];
        if (preg_match('#^https?://#i', $reference)) {
            $path = parse_url($reference, PHP_URL_PATH);
            if (is_string($path)) {
                $candidates[] = urldecode($path);
            }
        }

        $mediaDisk = Storage::disk('media');
        $mediaRoot = realpath($mediaDisk->path(''));
        if ($mediaRoot === false) {
            return null;
        }

        foreach (array_unique($candidates) as $candidate) {
            $key = preg_replace('#^/?static/media/#', '', $candidate);
            $key = ltrim($key, '/');
            if ($key === '' || str_contains($key, '\\') || preg_match('#(^|/)\.\.?(/|$)#', $key)) {
                continue;
            }
            $path = realpath($mediaDisk->path($key));

            if ($path !== false && is_file($path) && str_starts_with($path, $mediaRoot.DIRECTORY_SEPARATOR)) {
                return $path;
            }
        }

        return null;
    }
}
