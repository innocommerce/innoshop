<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Console;

use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\VideoPosterExtractor;
use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * media:generate-posters backfill: the candidate filter must mirror
 * VideoPosterService::isVideoMedia() — finfo reports mp4 as application/mp4
 * and API ingests may carry an empty mime, and those rows were invisible to
 * the old video/*-only filter (they rendered poster-less forever).
 */
class GenerateVideoPostersTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');

        $bytes = 'fake-poster-frame-bytes';
        $this->app->bind(VideoPosterExtractor::class, function () use ($bytes) {
            return new class($bytes) extends VideoPosterExtractor
            {
                public function __construct(private readonly string $posterBytes) {}

                public function extract(string $videoAbsolutePath, string $posterAbsolutePath, int $width = 0, int $height = 0): bool
                {
                    @mkdir(dirname($posterAbsolutePath), 0777, true);
                    file_put_contents($posterAbsolutePath, $this->posterBytes);

                    return true;
                }
            };
        });
    }

    #[Test]
    public function backfill_covers_application_mp4_and_extension_only_rows(): void
    {
        $weirdMime = $this->makeVideo('application/mp4', 'videos/backfill-a-'.uniqid().'.mp4');
        $emptyMime = $this->makeVideo(null, 'videos/backfill-b-'.uniqid().'.webm');
        $control   = $this->makeVideo('video/mp4', 'videos/backfill-c-'.uniqid().'.mp4');

        $this->artisan('media:generate-posters')->assertSuccessful();

        foreach ([$weirdMime, $emptyMime, $control] as $video) {
            $video->refresh();
            $this->assertNotNull($video->poster_media_id, "row {$video->storage_key} (mime {$video->mime}) must be backfilled");
        }
    }

    private function makeVideo(?string $mime, string $rawKey): MediaFile
    {
        $video = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/'.$rawKey,
            'mime'        => $mime,
            'source'      => 'upload',
        ]);
        Storage::disk('media')->put($rawKey, 'fake-video-bytes');

        return $video;
    }
}
