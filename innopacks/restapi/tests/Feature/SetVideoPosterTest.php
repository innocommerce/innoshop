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
use InnoShop\Common\Models\Admin;
use InnoShop\Common\Models\MediaFile;
use InnoShop\Common\Tests\TestCase;
use InnoShop\Restapi\Middleware\EnsureUserIsAdmin;
use InnoShop\Restapi\PanelApiControllers\MediaController;
use PHPUnit\Framework\Attributes\Test;

/**
 * Panel API endpoint for client-side poster frames (#291): the browser
 * captures a frame from the uploaded video (canvas) and posts it here; the
 * server stores, registers and links it as the video's poster — no ffmpeg
 * involved. `video` accepts a media id OR a raw storage key — legacy files
 * without a media_files row are adopted into one on the fly, so old raw-path
 * covers can gain posters (and a media:// reference) too.
 */
class SetVideoPosterTest extends TestCase
{
    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');

        // RestapiServiceProvider::boot() gates panel-api route registration on
        // installed(), whose static memo is already false by the time phpunit
        // migrations exist (the first app boot precedes them). Register the
        // one route under test with the real middleware stack instead.
        app('router')->middleware(['panel_api', 'auth:sanctum', EnsureUserIsAdmin::class])
            ->post('/api/panel/media/set-poster', [MediaController::class, 'setVideoPoster'])
            ->name('api.panel.media.set_poster');

        $this->admin = Admin::query()->create([
            'name'     => 'Poster Admin',
            'email'    => 'poster-admin-'.uniqid().'@test.local',
            'password' => 'password',
            'active'   => 1,
        ]);
    }

    #[Test]
    public function set_poster_links_uploaded_frame_and_returns_poster_url(): void
    {
        $video = $this->createVideo();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => $video->id,
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $video->refresh();
        $this->assertNotNull($video->poster_media_id, 'poster must be linked to the video');
        $this->assertNotSame('', (string) $response->json('data.poster_url'));
        $this->assertSame('media://'.$video->id, (string) $response->json('data.media_reference'));
    }

    #[Test]
    public function set_poster_adopts_legacy_storage_key_without_media_row(): void
    {
        // A file on disk that predates the media system: no media_files row.
        // The panel posts the key base64-encoded (clear-text media paths trip
        // local WAF layers into replaying POSTs as GETs). The disk-level lookup
        // must strip the static/media prefix — the media disk root sits below it.
        $rawKey    = 'videos/legacy-'.uniqid().'.mp4';
        $legacyKey = 'static/media/'.$rawKey;
        Storage::disk('media')->put($rawKey, 'fake-video-bytes');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'         => base64_encode($legacyKey),
                'video_encoded' => true,
                'poster'        => UploadedFile::fake()->image('poster.jpg'),
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $adopted = MediaFile::query()->where('storage_key', $legacyKey)->first();
        $this->assertNotNull($adopted, 'legacy file must be adopted into media_files');
        $this->assertNotNull($adopted->poster_media_id);
        $this->assertSame('media://'.$adopted->id, (string) $response->json('data.media_reference'));
    }

    #[Test]
    public function set_poster_accepts_media_reference_video(): void
    {
        // The panel form stores picked videos as media://{id} references — the
        // manual picker posts that reference back. resolveVideoMedia must
        // resolve it (the row's storage_key is the real path, not the ref).
        $video = $this->createVideo();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'         => base64_encode('media://'.$video->id),
                'video_encoded' => true,
                'poster'        => UploadedFile::fake()->image('poster.jpg'),
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $video->refresh();
        $this->assertNotNull($video->poster_media_id, 'media:// reference must resolve to the video');
        $this->assertNotSame('', (string) $response->json('data.poster_url'));
    }

    #[Test]
    public function set_poster_accepts_clear_text_storage_key_for_compat(): void
    {
        $rawKey    = 'videos/legacy-'.uniqid().'.mp4';
        $legacyKey = 'static/media/'.$rawKey;
        Storage::disk('media')->put($rawKey, 'fake-video-bytes');

        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => $legacyKey,
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $adopted = MediaFile::query()->where('storage_key', $legacyKey)->first();
        $this->assertNotNull($adopted);
        $this->assertNotNull($adopted->poster_media_id);
    }

    #[Test]
    public function set_poster_rejects_unknown_storage_key(): void
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => 'static/media/videos/ghost-'.uniqid().'.mp4',
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ]);

        $response->assertJsonPath('success', false);
    }

    #[Test]
    public function set_poster_rejects_non_video_media(): void
    {
        $image = MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/pictures/pic.jpg',
            'mime'        => 'image/jpeg',
            'source'      => 'upload',
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => $image->id,
                'poster' => UploadedFile::fake()->image('poster.jpg'),
            ]);

        $response->assertJsonPath('success', false);

        $image->refresh();
        $this->assertNull($image->poster_media_id);
    }

    #[Test]
    public function set_poster_replaces_existing_link(): void
    {
        $video = $this->createVideo();

        $first = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => $video->id,
                'poster' => UploadedFile::fake()->image('first.jpg', 200, 100),
            ]);
        // Distinct dimensions so the frames are not byte-identical — register()
        // checksum dedup would (correctly) collapse identical frames onto one row.
        $second = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => $video->id,
                'poster' => UploadedFile::fake()->image('second.jpg', 320, 240),
            ]);

        $first->assertOk();
        $second->assertOk();
        $this->assertNotSame(
            $first->json('data.poster_id'),
            $second->json('data.poster_id'),
            'a second pick must produce its own poster row'
        );

        $video->refresh();
        $this->assertSame($second->json('data.poster_id'), $video->poster_media_id);
    }

    #[Test]
    public function set_poster_rejects_oversized_base64_frame(): void
    {
        // The multipart channel caps images at max:4096 (KB); the base64
        // channel must have an equivalent ceiling, or a huge payload burns
        // memory in decode + getimagesize before any image check runs.
        $video     = $this->createVideo();
        $oversized = 'data:image/jpeg;base64,'.str_repeat('x', 5000000);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/panel/media/set-poster', [
                'video'         => $video->id,
                'poster_base64' => $oversized,
            ]);

        // The app renders ValidationException as a flat {success:false,
        // message} body (custom handler), so pin status + no-side-effect.
        $response->assertStatus(422);

        $video->refresh();
        $this->assertNull($video->poster_media_id, 'oversized base64 payload must be rejected before any poster is linked');
    }

    #[Test]
    public function set_poster_base64_does_not_leak_temp_files(): void
    {
        // uploadedFileFromDataUrl materializes the frame as a tempnam() copy;
        // nothing else owns it, so the endpoint must clean it up (and the
        // bare base file tempnam creates) after the link attempt.
        //
        // Windows quirks: tempnam truncates the prefix to 3 chars and adds a
        // .tmp suffix (poster_ -> posXXXX.tmp), and glob() needs forward
        // slashes while tempnam() returns backslashes — so compare basename
        // sets of both patterns.
        $tmp       = str_replace('\\', '/', sys_get_temp_dir());
        $snapshots = function () use ($tmp) {
            $files = array_merge(
                glob($tmp.'/poster_*') ?: [],
                glob($tmp.'/pos*.tmp*') ?: []
            );

            return array_map('basename', $files);
        };

        $video  = $this->createVideo();
        $before = $snapshots();

        $dataUrl = 'data:image/jpeg;base64,'.base64_encode(UploadedFile::fake()->image('frame.jpg')->getContent());
        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/panel/media/set-poster', [
                'video'         => $video->id,
                'poster_base64' => $dataUrl,
            ])->assertOk();

        $leaked = array_values(array_diff($snapshots(), $before));
        $this->assertSame([], $leaked, 'base64 poster materialization must not leak tempnam files');
    }

    #[Test]
    public function set_poster_accepts_base64_frame_without_file_upload(): void
    {
        // Some server stacks (WAF / proxy layers) intermittently mangle
        // multipart uploads; the panel posts the canvas frame as a data URL
        // instead. The server must decode it and link the poster the same way.
        $video   = $this->createVideo();
        $dataUrl = 'data:image/jpeg;base64,'.base64_encode(UploadedFile::fake()->image('frame.jpg')->getContent());

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/panel/media/set-poster', [
                'video'         => $video->id,
                'poster_base64' => $dataUrl,
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $video->refresh();
        $this->assertNotNull($video->poster_media_id, 'base64 frame must be decoded and linked');
        $this->assertNotSame('', (string) $response->json('data.poster_url'));
    }

    #[Test]
    public function set_poster_rejects_invalid_base64_frame(): void
    {
        $video = $this->createVideo();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/panel/media/set-poster', [
                'video'         => $video->id,
                'poster_base64' => 'data:image/jpeg;base64,'.base64_encode('this is not an image'),
            ]);

        $response->assertStatus(422)->assertJsonPath('success', false);

        $video->refresh();
        $this->assertNull($video->poster_media_id);
    }

    #[Test]
    public function set_poster_validates_image_type(): void
    {
        $video = $this->createVideo();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->post('/api/panel/media/set-poster', [
                'video'  => $video->id,
                'poster' => UploadedFile::fake()->createWithContent('note.txt', 'hello'),
            ]);

        $response->assertStatus(422);

        $video->refresh();
        $this->assertNull($video->poster_media_id);
    }

    private function createVideo(): MediaFile
    {
        return MediaFile::query()->create([
            'disk'        => 'local',
            'storage_key' => 'static/media/videos/probe-'.uniqid().'.mp4',
            'mime'        => 'video/mp4',
            'source'      => 'upload',
        ]);
    }
}
