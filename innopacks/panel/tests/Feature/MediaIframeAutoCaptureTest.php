<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Panel\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * The media manager uploads videos through its own Vue app (blade-embedded
 * JS, no Laravel app in panel tests) — the page must carry the same
 * client-side poster capture contract as the form component (#291): after a
 * video upload succeeds, an early frame is captured and posted to
 * media.set-poster. There is no JS runner here, so this pins the wiring at
 * the source level.
 */
class MediaIframeAutoCaptureTest extends TestCase
{
    private function mediaFooterSource(): string
    {
        $path = dirname(__DIR__, 2).'/resources/views/media/partials/_footer.blade.php';
        $this->assertFileExists($path);

        return (string) file_get_contents($path);
    }

    private function coverCapturePartialSource(): string
    {
        // After #291's partial extraction, the three browser-side helpers
        // live in innopacks/common's shared partial — read it directly so
        // the iframe contract can be asserted even though the iframe itself
        // only @includes it.
        $path = dirname(__DIR__, 3).'/common/resources/views/partials/inno_cover_capture_js.blade.php';
        $this->assertFileExists($path, 'shared cover-capture partial must exist');

        return (string) file_get_contents($path);
    }

    #[Test]
    public function media_upload_success_triggers_video_poster_capture(): void
    {
        $footer  = $this->mediaFooterSource();
        $partial = $this->coverCapturePartialSource();

        // The iframe pulls in the shared partial — same single source of
        // truth as the form component (#291). Drift here was the root
        // cause of the token-fallback and visibilityState-bail needing
        // to be applied twice.
        $this->assertStringContainsString("@include('common::partials.inno_cover_capture_js')", $footer, 'media footer must @include the shared partial');

        // Success handler wires the auto-capture with the upload response.
        $this->assertStringContainsString('this.autoCaptureVideoPoster(', $footer, 'upload success must invoke the capture hook');

        // The hook targets video payloads only and prefers the media id.
        $this->assertStringContainsString('autoCaptureVideoPoster(file)', $footer);
        $this->assertStringContainsString('file.media_id', $footer);

        // The shared partial carries the three browser-side helpers used
        // by both the form component and the media manager iframe.
        $this->assertStringContainsString('window.innoCaptureVideoFrame', $partial);
        $this->assertStringContainsString('window.innoSetVideoPoster', $partial);
        $this->assertStringContainsString('window.innoDrawContain', $partial);

        // Auth token resolution goes through the iframe's provider.
        $this->assertStringContainsString('window.urls && window.urls.panel_api', $partial, 'panel_api base must come from window.urls');

        // Retry chain must stop when the user closes/backgrounds the tab —
        // otherwise it hammers the server for up to 15s of background
        // fetches after a failure (#291).
        $this->assertStringContainsString('attempts < 6', $partial, 'retry bail must be gated on attempts < 6');
        $this->assertStringContainsString('document.visibilityState === \'hidden\'', $partial, 'retry bail must check visibilityState');
    }
}
