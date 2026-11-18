<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Traits;

use Exception;

/**
 * Cover-media behavior for models whose `image` column can hold either an
 * image or a video file (mp4/webm/ogg) — currently Article and Category.
 *
 * Video detection and poster resolution delegate to the media_is_video() /
 * media_poster_url() helpers in innopacks/common/helpers.php; the poster
 * frame itself is auto-extracted on upload by VideoPosterService.
 */
trait MediaCover
{
    /**
     * Get cover URL sized per call site.
     *
     * - Image covers: resized URL at the requested size.
     * - Video covers: resized poster-frame URL, so list cards never autoplay
     *   the video. Returns '' when no poster was extracted yet (extraction
     *   failed or legacy media) — callers render a play-icon placeholder
     *   instead of an <img> pointing at the video file, which browsers show
     *   as a broken image.
     *
     * @param  int  $width
     * @param  int  $height
     * @return string
     * @throws Exception
     */
    public function coverUrl(int $width = 800, int $height = 600): string
    {
        $image = (string) ($this->image ?? '');
        if ($image === '') {
            return '';
        }

        if ($this->is_video) {
            return media_poster_url($image, $width, $height);
        }

        return image_resize($image, $width, $height);
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getImageUrlAttribute(): string
    {
        return $this->getImageUrl();
    }

    /**
     * @param  int  $width
     * @param  int  $height
     * @return string
     * @throws Exception
     */
    public function getImageUrl(int $width = 600, int $height = 600): string
    {
        if ($this->is_video) {
            return image_origin($this->image);
        }

        return image_resize($this->image ?? '', $width, $height);
    }

    /**
     * Whether the cover image is a video file (mp4/webm/ogg). Delegates to the
     * media_is_video() helper; see there for detection details (media:// MIME
     * lookup first, extension fallback for raw paths).
     *
     * @return bool
     */
    public function getIsVideoAttribute(): bool
    {
        return media_is_video((string) ($this->image ?? ''));
    }
}
