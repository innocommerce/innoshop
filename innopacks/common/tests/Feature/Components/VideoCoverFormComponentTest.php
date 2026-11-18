<?php
/**
 * Copyright (c) Since 2024 InnoShop - All Rights Reserved
 *
 * @link       https://www.innoshop.com
 * @author     InnoShop <team@innoshop.com>
 * @license    https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 */

namespace InnoShop\Common\Tests\Feature\Components;

use InnoShop\Common\Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

/**
 * The pick-cover modal (#291) is blade-embedded JS — there is no JS test
 * runner in this repo, so these tests pin the three security/UX guards at
 * the emitted-script level: dynamic values must pass through innoEscapeAttr
 * (storage keys keep raw filenames), the confirm button must only unlock
 * after the first seeked frame is painted (a fast OK used to capture a blank
 * white canvas), and load/canvas-export failures must surface a hint instead
 * of silently bricking the modal.
 */
class VideoCoverFormComponentTest extends TestCase
{
    private function renderPickCoverScript(): string
    {
        $html = view()->file(__DIR__.'/../Fixtures/video-cover-page.blade.php', [
            'value' => 'static/media/videos/demo.mp4',
        ])->render();

        $this->assertStringContainsString('modal-pick-cover', $html, 'component render must include the pick-cover modal');
        $this->assertStringContainsString('pick-cover-hint-msg', $html, 'hint must carry a dedicated message span');

        $this->assertMatchesRegularExpression('/<script>([\s\S]*?)<\/script>/', $html, 'pushed pick-cover script must be present');

        return preg_replace('/^.*?<script>([\s\S]*?)<\/script>.*$/s', '$1', $html);
    }

    #[Test]
    public function video_cover_script_escapes_dynamic_values(): void
    {
        $script = $this->renderPickCoverScript();

        $this->assertMatchesRegularExpression('/function innoEscapeAttr\(/', $script, 'an HTML-attribute escaper must exist');

        // Storage keys preserve original filenames, so every dynamic value
        // interpolated into string-built HTML is attacker-controllable.
        foreach (['+ originUrl +', '+ posterUrl +', '+ videoSrc +', '+ src +', '+ url +'] as $raw) {
            $this->assertStringNotContainsString($raw, $script, "dynamic value ({$raw}) must never be interpolated unescaped");
        }

        $renderBody = $this->between($script, 'function innoRenderVideoCover', "\n  }");
        $this->assertStringContainsString('innoEscapeAttr(posterUrl)', $renderBody);
        $this->assertStringContainsString('innoEscapeAttr(originUrl)', $renderBody);
    }

    #[Test]
    public function pick_cover_confirm_gates_on_first_painted_frame(): void
    {
        $script = $this->renderPickCoverScript();

        // Confirm must NOT unlock on loadeddata: at that point the initial
        // seek has not painted yet and the canvas is still blank white.
        $loadedBlock = $this->between($script, ".on('loadeddata.pick-cover'", ".on('seeked.pick-cover'");
        $this->assertStringNotContainsString("confirmBtn.prop('disabled', false)", $loadedBlock, 'loadeddata must not unlock confirm');

        // The first seeked paint unlocks it, right after drawing.
        $seekedBlock = $this->between($script, ".on('seeked.pick-cover'", 'range.off(');
        $this->assertStringContainsString('innoDrawContain(video, canvas)', $seekedBlock);
        $this->assertStringContainsString("confirmBtn.prop('disabled', false)", $seekedBlock, 'confirm unlocks only after a painted frame');
    }

    #[Test]
    public function pick_cover_surfaces_load_and_export_failures(): void
    {
        $script = $this->renderPickCoverScript();

        // Video load failure: show a hint instead of a dead modal with a
        // forever-disabled confirm button.
        $errorBlock = $this->between($script, ".on('error.pick-cover'", ".on('loadeddata.pick-cover'");
        $this->assertStringContainsString("confirmBtn.prop('disabled', true)", $errorBlock);
        $this->assertStringContainsString('capture_load_failed', $errorBlock);

        // Canvas export: toDataURL throws SecurityError on tainted (cross-
        // origin) canvases — it must be guarded, or the OK click bricks.
        $confirmBlock = $this->between($script, "confirmBtn.off('click.pick-cover'", "modal.modal('show')");
        $this->assertStringContainsString('try {', $confirmBlock);
        $this->assertStringContainsString("canvas.toDataURL('image/jpeg', 0.85)", $confirmBlock);
        $this->assertStringContainsString('capture_cross_origin', $confirmBlock, 'tainted-canvas export must surface the cross-origin hint');
    }

    #[Test]
    public function set_poster_retries_bail_when_tab_is_hidden(): void
    {
        $script = $this->renderPickCoverScript();

        // The retry chain runs up to 6 × 2.5s of background fetches after
        // a failure. Once the user has closed or backgrounded the tab,
        // every retry is pure waste (#291). The bail must fire on retry
        // (attempts < 6) only — the initial user-triggered call still has
        // to go through even if the page happens to be hidden at that
        // moment (browser may have just suspended the foreground state).
        // After the partial-extraction refactor the helpers are attached
        // as window.innoX = function (...) rather than bare declarations.
        $posterBlock = $this->between($script, 'window.innoSetVideoPoster = function', '.then((result) => {');
        $this->assertStringContainsString('attempts < 6', $posterBlock, 'bail must be gated on a retry, not the initial call');
        $this->assertStringContainsString('document.visibilityState === \'hidden\'', $posterBlock, 'bail must check visibilityState');
    }

    #[Test]
    public function form_pulls_in_shared_cover_capture_partial(): void
    {
        // After #291's partial extraction, the form component must not
        // reimplement innoCaptureVideoFrame / innoSetVideoPoster /
        // innoDrawContain — instead it @includes the shared partial so
        // the form and the media manager iframe stay in lock-step.
        $html = view()->file(__DIR__.'/../Fixtures/video-cover-page.blade.php', [
            'value' => 'static/media/videos/demo.mp4',
        ])->render();

        $this->assertStringContainsString('window.innoCaptureVideoFrame = function', $html, 'shared partial must be inlined into the rendered form HTML');
        $this->assertStringContainsString('window.innoSetVideoPoster = function', $html, 'shared partial must be inlined into the rendered form HTML');
        $this->assertStringContainsString('window.innoDrawContain = function', $html, 'drawing helper unified name must come from the partial');
    }

    /**
     * Return the substring between two markers (first occurrence of each).
     */
    private function between(string $haystack, string $start, string $end): string
    {
        $from = strpos($haystack, $start);
        if ($from === false) {
            $this->fail("marker not found in script: {$start}");
        }

        $from += strlen($start);
        $to = strpos($haystack, $end, $from);
        if ($to === false) {
            $this->fail("marker not found in script: {$end}");
        }

        return substr($haystack, $from, $to - $from);
    }
}
