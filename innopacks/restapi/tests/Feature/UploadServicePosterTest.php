<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Restapi\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Services\VideoPosterService;
use InnoShop\Common\Tests\TestCase;
use InnoShop\Restapi\Services\UploadService;
use PHPUnit\Framework\Attributes\Test;

/**
 * Direct uploads (panel/front UploadController) register a media_files row
 * but never extract a poster server-side (#291): poster production moved to
 * the browser (canvas capture → media.set_poster). ffmpeg remains an opt-in
 * dependency of the media:generate-posters backfill command only.
 *
 * The mp4 magic bytes make finfo report video/mp4 for the fake upload.
 */
class UploadServicePosterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
    }

    #[Test]
    public function video_upload_registers_media_row_without_poster(): void
    {
        $file = UploadedFile::fake()->createWithContent(
            'clip.mp4',
            "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom"
        );

        $result = UploadService::getInstance()->uploadFile($file, 'common');

        $this->assertArrayHasKey('value', $result);

        $media = MediaFile::query()->where('storage_key', $result['value'])->first();
        $this->assertNotNull($media, 'video upload must register a media_files row');
        $this->assertNull($media->poster_media_id, 'poster production is client-side now');
    }

    #[Test]
    public function image_upload_registers_media_row_without_poster(): void
    {
        $file = UploadedFile::fake()->image('photo.jpg');

        $result = UploadService::getInstance()->uploadFile($file, 'common');

        $this->assertArrayHasKey('value', $result);

        $media = MediaFile::query()->where('storage_key', $result['value'])->first();
        $this->assertNotNull($media);
        $this->assertNull($media->poster_media_id);
    }

    #[Test]
    public function video_upload_attempts_server_side_poster_extraction_as_fallback(): void
    {
        // Panel uploads cover the poster client-side (canvas → media.set_poster),
        // but REST / CLI / front UploadController callers cannot (#291). The
        // server-side extraction is the fallback so non-panel ingest still
        // ends up with a poster when ffmpeg is present.
        //
        // The fallback is best-effort: in the test env ffmpeg is absent and
        // the extraction short-circuits with a log warning. We bind a fake
        // VideoPosterService that records calls so the wiring is what the
        // assertion pins — not the extraction outcome (which depends on ffmpeg).
        $called = false;
        $this->app->bind(VideoPosterService::class, function () use (&$called) {
            $called = true;

            return new VideoPosterService;
        });

        $file = UploadedFile::fake()->createWithContent(
            'clip.mp4',
            "\x00\x00\x00\x18ftypmp42\x00\x00\x00\x00mp42isom"
        );

        $result = UploadService::getInstance()->uploadFile($file, 'common');

        $media = MediaFile::query()->where('storage_key', $result['value'])->first();
        $this->assertNotNull($media, 'video upload must still register a media row');
        $this->assertTrue(
            $called,
            'video upload must attempt the server-side poster fallback so non-panel callers get a poster',
        );
    }
}
