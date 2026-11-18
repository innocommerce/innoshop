<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Requests\UploadFileRequest;
use InnoShop\Common\Requests\UploadImageRequest;
use InnoShop\Common\Services\FileSecurityValidator;
use InnoShop\Common\Services\ImageDownscaler;
use InnoShop\Common\Services\MediaUrlResolver;
use InnoShop\Common\Services\StorageService;
use InnoShop\Common\Services\VideoPosterService;
use InnoShop\Front\Services\BaseService;

class UploadService extends BaseService
{
    /**
     * Generic upload method for different file types and storage disks.
     * Always returns storage key as value; URLs generated via storage_url().
     *
     * @param  mixed  $file  The uploaded file
     * @param  string  $type  File type/directory
     * @return array
     */
    public function uploadFile($file, string $type = 'common'): array
    {
        FileSecurityValidator::validateFile($file->getClientOriginalName());

        // Shrink before the file touches the disk: an untouched 6000px+
        // original cannot get a thumbnail later (ImageService stops at 5000px)
        // and is then served multi-MB on every page render.
        $this->downscaleUploadedImage($file);

        $filePath   = $file->store("/{$type}", 'media');
        $storageKey = StorageService::storageKey($filePath);

        $this->registerMediaFile($file, $storageKey);

        return [
            'url'        => storage_url($storageKey),
            'origin_url' => storage_url($storageKey),
            'value'      => $storageKey,
        ];
    }

    /**
     * Rewrite the uploaded temp file at a sane resolution. Never throws: the
     * upload itself must survive a resize that cannot be done.
     */
    protected function downscaleUploadedImage($file): void
    {
        try {
            $path = $file->getRealPath();
            if (is_string($path) && $path !== '') {
                ImageDownscaler::shrink($path);
            }
        } catch (\Throwable $e) {
            Log::warning('Uploaded image downscale failed: '.$e->getMessage());
        }
    }

    /**
     * Write a media_files record for the uploaded file, and best-effort
     * extract a poster for video uploads. Failures are logged but do not
     * block the upload.
     *
     * Panel media manager covers the poster via client-side capture (canvas
     * → media.set_poster); REST / front UploadController traffic does not.
     * A short, ffmpeg-driven server-side fallback covers those callers
     * without forcing the deploy to depend on ffmpeg — the failure mode is
     * simply "no poster row", which the operator can backfill later via
     * `php artisan media:generate-posters` (#291).
     */
    protected function registerMediaFile($file, string $storageKey): void
    {
        $mediaFile = null;
        try {
            $disk     = system_setting('media_driver', 'local');
            $realPath = $file->getRealPath();

            // getSize() reports the original upload, which no longer matches
            // once the temp file has been shrunk in place.
            $extra = (is_string($realPath) && is_file($realPath)) ? ['size' => filesize($realPath)] : [];

            $mediaFile = MediaUrlResolver::getInstance()->registerFromUploadedFile($file, $storageKey, $disk, $extra);
        } catch (\Throwable $e) {
            Log::warning('Media register failed: '.$e->getMessage(), ['storage_key' => $storageKey]);
        }

        if ($mediaFile && self::isVideoUpload($file)) {
            try {
                app(VideoPosterService::class)->ensurePoster($mediaFile);
            } catch (\Throwable $e) {
                Log::warning('Video poster fallback failed: '.$e->getMessage(), [
                    'storage_key' => $storageKey,
                ]);
            }
        }
    }

    /**
     * Whether the upload looks like a video. Mirrors
     * VideoPosterService::isVideoMedia(): positive video MIME first,
     * extension fallback when finfo reports `application/mp4` (the IANA
     * type for mp4) instead of `video/mp4`.
     */
    private static function isVideoUpload($file): bool
    {
        $mime = (string) ($file->getMimeType() ?: '');
        if (str_starts_with($mime, 'video/')) {
            return true;
        }

        return (bool) preg_match(
            '/\.(mp4|webm|ogg)$/i',
            (string) $file->getClientOriginalName()
        );
    }

    /**
     * Upload images.
     *
     * @param  UploadImageRequest  $request
     * @return array
     */
    public function images(UploadImageRequest $request): array
    {
        $image = $request->file('image');
        $type  = $request->file('type', 'common');

        return $this->uploadFile($image, $type);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return array
     */
    public function docs(UploadFileRequest $request): array
    {
        $file = $request->file('file');
        $type = $request->file('type', 'docs');

        return $this->uploadFile($file, $type);
    }

    /**
     * Upload document files
     *
     * @param  UploadFileRequest  $request
     * @return array
     */
    public function files(UploadFileRequest $request): array
    {
        $file = $request->file('file');
        $type = $request->file('type', 'files');

        return $this->uploadFile($file, $type);
    }
}
